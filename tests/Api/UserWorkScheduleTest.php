<?php


namespace App\Tests\Api;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\UserWorkScheduleFixtures;
use App\DataFixtures\UserWorkScheduleStatusFixtures;
use App\Entity\UserWorkSchedule;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use DateTime;
use Exception;

/**
 * Class UserWorkScheduleTest
 */
class UserWorkScheduleTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetUserWorkSchedules(): void
    {
        $userWorkScheduleDB = $this->entityManager
            ->getRepository(UserWorkSchedule::class)
            ->createQueryBuilder('p')
            ->andWhere('p.owner = :owner')
            ->setParameter('owner', $this->fixtures->getReference(UserFixtures::REF_USER_MANAGER))
            ->getQuery()
            ->getResult();
        /* @var $userWorkScheduleDB UserWorkSchedule */

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_work_schedules',
            null,
            [],
            200,
            UserFixtures::REF_USER_MANAGER
        );
        $userWorkScheduleJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userWorkScheduleJSON);
        $this->assertEquals(count($userWorkScheduleDB), $userWorkScheduleJSON->{'hydra:totalItems'});
    }

    /**
     * @test
     * @dataProvider apiGetUserWorkScheduleProvider
     * @param string $referenceName
     * @throws NotFoundReferencedUserException
     */
    public function apiGetUserWorkSchedule($referenceName): void
    {
        $userWorkScheduleDB = $this->fixtures->getReference($referenceName);
        /* @var $userWorkScheduleDB UserWorkSchedule */

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_work_schedules/' . $userWorkScheduleDB->getId()
        );
        $userWorkScheduleJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userWorkScheduleJSON);
        $this->assertEquals($userWorkScheduleDB->getId(), $userWorkScheduleJSON->id);
        $this->assertEquals($userWorkScheduleDB->getFromDate(), new DateTime($userWorkScheduleJSON->fromDate));
        $this->assertEquals($userWorkScheduleDB->getToDate(), new DateTime($userWorkScheduleJSON->toDate));
        $this->assertEquals($userWorkScheduleDB->getStatus()->getId(), $userWorkScheduleJSON->status->id);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function apiGetUserWorkScheduleProvider(): array
    {
        $referenceList = [
            ['user_work_schedule_admin_hr'],
            ['user_work_schedule_admin_edit'],
            ['user_work_schedule_manager_hr'],
            ['user_work_schedule_user_hr'],
            ['user_work_schedule_user_owner_accept'],
        ];

        return $referenceList;
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiPostUserWorkSchedule(): void
    {
        $userRef = $this->fixtures->getReference(UserFixtures::REF_USER_USER);
        /* @var $userRef User */

        $workScheduleProfileRef = $this->fixtures->getReference('work_schedule_profile_4');
        $workScheduleStatusRef = $this->fixtures->getReference(UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT);
        /* @var $workScheduleProfileRef WorkScheduleProfile */

        $payload = <<<JSON
{
    "fromDate": "2019-08-01",
    "toDate": "2019-08-31",
    "status": "/api/user_work_schedule_statuses/{$workScheduleStatusRef->getId()}",
    "owner": "/api/users/{$userRef->getId()}",
    "workScheduleProfile": "/api/work_schedule_profiles/{$workScheduleProfileRef->getId()}"
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_POST,
            '/api/user_work_schedules',
            $payload,
            [],
            201
        );

        $userWorkScheduleJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userWorkScheduleJSON);
        $this->assertIsNumeric($userWorkScheduleJSON->id);
        $this->assertEquals(new DateTime('2019-08-01'), new DateTime($userWorkScheduleJSON->fromDate));
        $this->assertEquals(new DateTime('2019-08-31'), new DateTime($userWorkScheduleJSON->toDate));
        $this->assertEquals($workScheduleStatusRef->getId(), $userWorkScheduleJSON->status->id);
        $this->assertEquals($userRef->getId(), $userWorkScheduleJSON->owner->id);
        $this->assertEquals($workScheduleProfileRef->getId(), $userWorkScheduleJSON->workScheduleProfile->id);

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_work_schedules/' . $userWorkScheduleJSON->id
        );

        $userWorkScheduleDB = $this->entityManager->getRepository(UserWorkSchedule::class)->find(
            $userWorkScheduleJSON->id
        );
        /* @var $userWorkScheduleDB UserWorkSchedule */

        $userWorkScheduleJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userWorkScheduleJSON);
        $this->assertEquals($userWorkScheduleDB->getId(), $userWorkScheduleJSON->id);
        $this->assertEquals($userWorkScheduleDB->getFromDate(), new DateTime($userWorkScheduleJSON->fromDate));
        $this->assertEquals($userWorkScheduleDB->getToDate(), new DateTime($userWorkScheduleJSON->toDate));
        $this->assertEquals($userWorkScheduleDB->getStatus()->getId(), $userWorkScheduleJSON->status->id);
        $this->assertEquals(
            $userWorkScheduleDB->getWorkScheduleProfile()->getId(),
            $userWorkScheduleJSON->workScheduleProfile->id
        );
        $this->assertEquals(
            $userWorkScheduleDB->getOwner()->getId(),
            $userWorkScheduleJSON->owner->id
        );
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     * @throws Exception
     */
    public function apiPutUserWorkSchedule(): void
    {
        $user = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        /**
         * Login as ROLE_ADMIN
         */
        $this->loginAsUser($user, ['ROLE_ADMIN']);

        $userWorkScheduleREF = $this
            ->fixtures
            ->getReference(UserWorkScheduleFixtures::REF_USER_WORK_SCHEDULE_MANAGER_HR)
        ;

        $workScheduleStatusRef = $this
            ->fixtures
            ->getReference(UserWorkScheduleStatusFixtures::REF_STATUS_MANAGER_ACCEPT)
        ;


        $payload = <<<JSON
{
    "status": "/api/user_work_schedule_statuses/{$workScheduleStatusRef->getId()}"
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_PUT,
            '/api/user_work_schedules/' . $userWorkScheduleREF->getId(),
            $payload,
            [],
            200,
            UserFixtures::REF_USER_ADMIN
        );

        $userJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userJSON);
        $this->assertIsNumeric($userJSON->id);
        $this->assertEquals($workScheduleStatusRef->getId(), $userJSON->status->id);

        $userWorkScheduleDB = $this->entityManager->getRepository(UserWorkSchedule::class)->find(
            $userWorkScheduleREF->getId()
        );
        /* @var $userWorkScheduleDB UserWorkSchedule */

        $userJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userJSON);
        $this->assertEquals($userWorkScheduleDB->getId(), $userJSON->id);
        $this->assertEquals($userWorkScheduleDB->getStatus()->getId(), $userJSON->status->id);
    }
}
