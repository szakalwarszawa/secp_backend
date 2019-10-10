<?php

declare(strict_types=1);

namespace App\Tests\Serializer;

use App\Tests\AbstractWebTestCase;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\UserTimesheetFixtures;
use App\DataFixtures\UserWorkScheduleFixtures;
use App\Entity\UserTimesheetDay;
use App\Entity\UserTimesheetDayLog;
use App\Entity\UserWorkScheduleDay;
use Symfony\Component\VarDumper\VarDumper;
use function strlen;

/**
 * Class UserTimesheetDayLogNormalizerTest
 */
class UserTimesheetDayLogNormalizerTest extends AbstractWebTestCase
{
    /**
     * Test UserTimesheetDayLogNormalizer class.
     *
     * @return void
     */
    public function testUserTimesheetDayLogNormalizer(): void
    {
        /**
         * Create UserTimesheetDayLog by change absence & presence type
         */
        $userTimesheetDay = $this->entityManager
            ->getRepository(UserTimesheetDay::class)
            ->createQueryBuilder('p')
            ->innerJoin('p.userTimesheet', 'userTimesheet')
            ->andWhere('userTimesheet.owner = :owner')
            ->setParameter('owner', $this->fixtures->getReference(UserFixtures::REF_USER_USER))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $presenceTypeRef = $this->getEntityFromReference('presence_type_5');
        $absenceTypeRef = $this->getEntityFromReference('absence_type_7');
        $userTimesheetDay
            ->setPresenceType($presenceTypeRef)
            ->setAbsenceType($absenceTypeRef)
        ;

        $this->entityManager->flush();
        /**
         * Logs created
         */

        $userManager = $this->fixtures->getReference(UserFixtures::REF_USER_MANAGER);
        $this->loginAsUser($userManager, ['ROLE_DEPARTMENT_MANAGER']);

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_timesheet_days/' . $userTimesheetDay->getId() . '/logs',
            null,
            [],
            200,
            UserFixtures::REF_USER_MANAGER
        );

        $timesheetDayLogsJSON = json_decode($response->getContent(), false);

        /**
         * At this point (API called as User Manager) absenceType should be not visible.
         * Notice does not contain new absence type name.
         */
        $this->assertNotNull($timesheetDayLogsJSON);
        $logs = $timesheetDayLogsJSON->{'hydra:member'};
        $this->assertEquals('Zmieniono typ nieobecności', $logs[1]->notice);

        /**
         * Call API as ROLE_HR - absenceType should be visible.
         */
        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_timesheet_days/' . $userTimesheetDay->getId() . '/logs',
            null,
            [],
            200,
            UserFixtures::REF_USER_HR_MANAGER
        );

        $timesheetDayLogsJSON = json_decode($response->getContent(), false);

        $logs = $timesheetDayLogsJSON->{'hydra:member'};
        /**
         * Log notice should contain new absence type name.
         */
        $this->assertStringContainsString($absenceTypeRef->getName(), $logs[1]->notice);
    }
}
