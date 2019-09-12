<?php

declare(strict_types=1);

namespace App\Tests\Serializer;

use App\Tests\AbstractWebTestCase;
use App\DataFixtures\UserFixtures;
use App\Entity\UserTimesheetDay;

/**
 * Class UserRoleValidatorTest
 *
 * It test:
 *  App\Serializer =>
 *      UserContextBuilder::class,
 *      UserTimesheetDayNormalizer::class,
 *      GroupsRestrictions\Input
 *      GroupsRestrictions\Output
 *      GroupsRestrictions\
 */
class UserContextBuilderTest extends AbstractWebTestCase
{
    /**
     * Test UserContextBuilder class.
     *
     * @return void
     */
    public function testUserContextBuilder(): void
    {
        $userAdmin = $this->fixtures->getReference(UserFixtures::REF_USER_MANAGER);
        $this->loginAsUser($userAdmin, ['ROLE_HR']);

        $this->assertTrue($this->security->isGranted('ROLE_HR'));

       $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_timesheet_days',
            null,
            [],
            200,
            self::REF_MANAGER
        );

        $this->assertNotNull($this->lastActionRequest);
        $userContextBuilder = self::$container->get('App\Serializer\UserContextBuilder');
        $context = $userContextBuilder->createFromRequest($this->lastActionRequest, true);

        $this->assertTrue(is_array($context));
        $this->assertArrayHasKey('resource_class', $context);
        $this->assertArrayHasKey('request_uri', $context);
        $this->assertArrayHasKey('groups', $context);

        $this->assertSame('/api/user_timesheet_days', $context['request_uri']);
        $this->assertSame(UserTimesheetDay::class, $context['resource_class']);

        $this->assertTrue(in_array('hr:output', $context['groups']));

        $userTimesheetDaysJSON = json_decode($response->getContent(), false);
        $elements = $userTimesheetDaysJSON->{'hydra:member'};
        foreach ($elements as $element) {
            /**
             * At this moment user has ROLE_HR, so able to see AbsenceType->name and ->shortName
             */
            if ($element->absenceType) {
                $this->assertNotEmpty($element->absenceType->name);
                $this->assertNotEmpty($element->absenceType->shortName);
            }
        }

        $userAdmin = $this->fixtures->getReference(UserFixtures::REF_USER_MANAGER);

        $this->loginAsUser($userAdmin, ['ROLE_DEPARTMENT_MANAGER']);
        $this->assertFalse($this->security->isGranted('ROLE_HR'));
        $this->assertTrue($this->security->isGranted('ROLE_DEPARTMENT_MANAGER'));

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_timesheet_days',
            null,
            [],
            200,
            self::REF_MANAGER
        );

        $userTimesheetDaysJSON = json_decode($response->getContent(), false);
        $elements = $userTimesheetDaysJSON->{'hydra:member'};
        foreach ($elements as $element) {
            /**
             * At this moment user has ROLE_DEPARTMENT, so able to see users from his
             * department BUT should not to see thier absence reasons.
             *
             * Ofc User can see OWN absence reason all the time.
             */
            if ($element->presenceType && 'N' === $element->presenceType->shortName) {
                if ($element->userTimesheet->owner->username !== $userAdmin->getUsername()) {
                    $this->assertObjectNotHasAttribute('absenceType', $element);
                }

                if ($element->userTimesheet->owner->username === $userAdmin->getUsername()) {
                    $this->assertObjectHasAttribute('absenceType', $element);
                }
            }
        }
    }
}
