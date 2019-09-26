<?php


namespace App\Tests\Api;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\UserTimesheetFixtures;
use App\DataFixtures\UserTimesheetStatusFixtures;
use App\Entity\User;
use App\Entity\UserTimesheet;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use Exception;

class UserTimesheetTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetUserTimesheets(): void
    {
        $userTimesheetDB = $this->entityManager->getRepository(UserTimesheet::class)->createQueryBuilder('p')
            ->andWhere('p.owner = :owner')
            ->setParameter('owner', $this->fixtures->getReference(UserFixtures::REF_USER_MANAGER))
            ->getQuery()
            ->getResult();
        /* @var $userTimesheetDB UserTimesheet */
        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_timesheets',
            null,
            [],
            200,
            self::REF_MANAGER
        );
        $userTimesheetJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userTimesheetJSON);
        $this->assertEquals(count($userTimesheetDB), $userTimesheetJSON->{'hydra:totalItems'});
    }

    /**
     * @test
     * @dataProvider apiGetUserTimesheetProvider
     * @param string $referenceName
     * @throws NotFoundReferencedUserException
     */
    public function apiGetUserTimesheet($referenceName): void
    {
        $userTimesheetDB = $this->fixtures->getReference($referenceName);
        /* @var $userTimesheetDB UserTimesheet */

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_timesheets/' . $userTimesheetDB->getId()
        );
        $this->assertJson($response->getContent());
        $userTimesheetJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userTimesheetJSON);
        $this->assertEquals($userTimesheetDB->getId(), $userTimesheetJSON->id);
        $this->assertEquals($userTimesheetDB->getPeriod(), $userTimesheetJSON->period);
        $this->assertEquals($userTimesheetDB->getOwner()->getId(), $userTimesheetJSON->owner->id);
        $this->assertEquals($userTimesheetDB->getStatus()->getId(), $userTimesheetJSON->status->id);
    }

    /**
     * @test
     * @dataProvider apiGetUserTimesheetProvider
     * @param string $referenceName
     * @param string $referenceUserName
     * @throws NotFoundReferencedUserException
     */
    public function apiGetUserTimesheetWithDays($referenceName, $referenceUserName): void
    {
        $userTimesheetDB = $this->fixtures->getReference($referenceName);
        /* @var $userTimesheetDB UserTimesheet */

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_timesheets/' . $userTimesheetDB->getId() . '/user_timesheet_days',
            null,
            [],
            200,
            $referenceUserName
        );
        $this->assertJson($response->getContent());
        $userTimesheetJSON = json_decode($response->getContent(), false);
        $this->assertNotNull($userTimesheetJSON);

        $this->assertEquals(count($userTimesheetDB->getUserTimesheetDays()), $userTimesheetJSON->{'hydra:totalItems'});
        $this->assertCount(count($userTimesheetDB->getUserTimesheetDays()), $userTimesheetJSON->{'hydra:member'});
    }

    /**
     * @return array
     * @throws Exception
     */
    public function apiGetUserTimesheetProvider(): array
    {
        $referenceList = [
            ['user_timesheet_admin_edit', UserFixtures::REF_USER_ADMIN],
            ['user_timesheet_manager_hr', UserFixtures::REF_USER_MANAGER],
            ['user_timesheet_manager_edit', UserFixtures::REF_USER_MANAGER],
            ['user_timesheet_user_hr', UserFixtures::REF_USER_USER],
            ['user_timesheet_user_edit', UserFixtures::REF_USER_USER],
        ];

        return $referenceList;
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiPostUserTimesheet(): void
    {
        $userRef = $this->fixtures->getReference(UserFixtures::REF_USER_USER);
        /* @var $userRef User */

        $timesheetStatusRef = $this->fixtures->getReference(UserTimesheetStatusFixtures::REF_STATUS_OWNER_EDIT);

        $payload = <<<JSON
{
    "owner": "/api/users/{$userRef->getId()}",
    "period": "2019-08",
    "status": "/api/user_timesheet_statuses/{$timesheetStatusRef->getId()}"
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_POST,
            '/api/user_timesheets',
            $payload,
            [],
            201,
            self::REF_ADMIN
        );

        $userTimesheetJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userTimesheetJSON);
        $this->assertIsNumeric($userTimesheetJSON->id);
        $this->assertEquals($userRef->getId(), $userTimesheetJSON->owner->id);
        $this->assertEquals('2019-08', $userTimesheetJSON->period);
        $this->assertEquals($timesheetStatusRef->getId(), $userTimesheetJSON->status->id);

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_timesheets/' . $userTimesheetJSON->id
        );

        $userTimesheetDB = $this->entityManager->getRepository(UserTimesheet::class)->find(
            $userTimesheetJSON->id
        );
        /* @var $userTimesheetDB UserTimesheet */

        $userTimesheetJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userTimesheetJSON);
        $this->assertEquals($userTimesheetDB->getId(), $userTimesheetJSON->id);
        $this->assertEquals(
            $userTimesheetDB->getOwner()->getId(),
            $userTimesheetJSON->owner->id
        );
        $this->assertEquals($userTimesheetDB->getPeriod(), $userTimesheetJSON->period);
        $this->assertEquals($userTimesheetDB->getStatus()->getId(), $userTimesheetJSON->status->id);
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     * @throws Exception
     */
    public function apiPutUserTimesheet(): void
    {
        $userTimesheetREF = $this->fixtures->getReference(UserTimesheetFixtures::REF_USER_TIMESHEET_USER_EDIT);
        /* @var $userTimesheetREF UserTimesheet */

        $timesheetStatusRef = $this->fixtures->getReference(UserTimesheetStatusFixtures::REF_STATUS_OWNER_ACCEPT);

        $payload = <<<JSON
{
    "status": "/api/user_timesheet_statuses/{$timesheetStatusRef->getId()}"
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_PUT,
            '/api/user_timesheets/' . $userTimesheetREF->getId(),
            $payload,
            [],
            200,
            UserFixtures::REF_USER_USER
        );

        $userJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userJSON);
        $this->assertIsNumeric($userJSON->id);
        $this->assertEquals($timesheetStatusRef->getId(), $userJSON->status->id);

        $userTimesheetDB = $this->entityManager->getRepository(UserTimesheet::class)->find(
            $userTimesheetREF->getId()
        );
        /* @var $userTimesheetDB UserTimesheet */

        $userJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userJSON);
        $this->assertEquals($userTimesheetDB->getId(), $userJSON->id);
        $this->assertEquals($userTimesheetDB->getStatus()->getId(), $userJSON->status->id);
    }
}
