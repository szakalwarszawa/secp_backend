<?php


namespace App\Tests\Api;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\WorkScheduleProfile;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use Exception;

class UserWorkScheduleTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetUserWorkSchedules(): void
    {
        $userWorkScheduleDB = $this->entityManager->getRepository(UserWorkSchedule::class)->findAll();
        /* @var $userWorkScheduleDB UserWorkSchedule */
        $response = $this->getActionResponse(self::HTTP_GET, '/api/user_work_schedules');
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
        $this->assertEquals($userWorkScheduleDB->getFromDate(), new \DateTime($userWorkScheduleJSON->fromDate));
        $this->assertEquals($userWorkScheduleDB->getToDate(), new \DateTime($userWorkScheduleJSON->toDate));
        $this->assertEquals($userWorkScheduleDB->getStatus(), $userWorkScheduleJSON->status);
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
        /* @var $workScheduleProfileRef WorkScheduleProfile */

        $payload = <<<JSON
{
    "fromDate": "2019-08-01",
    "toDate": "2019-08-31",
    "status": 0,
    "owner": "/api/users/{$userRef->getId()}",
    "workScheduleProfile": "/api/work_schedule_profiles/{$workScheduleProfileRef->getId()}"
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_POST,
            '/api/user_work_schedules',
            $payload,
            [],
            201,
            self::REF_ADMIN
        );

        $userWorkScheduleJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userWorkScheduleJSON);
        $this->assertIsNumeric($userWorkScheduleJSON->id);
        $this->assertEquals(new \DateTime('2019-08-01'), new \DateTime($userWorkScheduleJSON->fromDate));
        $this->assertEquals(new \DateTime('2019-08-31'), new \DateTime($userWorkScheduleJSON->toDate));
        $this->assertEquals(0, $userWorkScheduleJSON->status);
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
        $this->assertEquals($userWorkScheduleDB->getFromDate(), new \DateTime($userWorkScheduleJSON->fromDate));
        $this->assertEquals($userWorkScheduleDB->getToDate(), new \DateTime($userWorkScheduleJSON->toDate));
        $this->assertEquals($userWorkScheduleDB->getStatus(), $userWorkScheduleJSON->status);
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
        $userWorkScheduleREF = $this->fixtures->getReference(UserFixtures::REF_USER_MANAGER);
        /* @var $userWorkScheduleREF UserWorkSchedule */

        $userREF = $this->fixtures->getReference(UserFixtures::REF_USER_MANAGER);
        /* @var $userREF User */

        $newStatus = UserWorkSchedule::STATUS_MANAGER_ACCEPT;

        $payload = <<<JSON
{
    "status": {$newStatus}
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_PUT,
            '/api/user_work_schedules/' . $userWorkScheduleREF->getId(),
            $payload,
            [],
            200,
            UserFixtures::REF_USER_MANAGER
        );

        $userJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userJSON);
        $this->assertIsNumeric($userJSON->id);
        $this->assertEquals($newStatus, $userJSON->status);

        $userWorkScheduleDB = $this->entityManager->getRepository(UserWorkSchedule::class)->find(
            $userWorkScheduleREF->getId()
        );
        /* @var $userWorkScheduleDB UserWorkSchedule */

        $userJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userJSON);
        $this->assertEquals($userWorkScheduleDB->getId(), $userJSON->id);
        $this->assertEquals($userWorkScheduleDB->getStatus(), $userJSON->status);
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     * @throws Exception
     */
    public function apiPutUserWithManagedDepartment(): void
    {
        $userREF = $this->fixtures->getReference('user_' . random_int(0, 99));
        /* @var $userREF User */

        $defaultWorkScheduleProfileREF = $this->fixtures->getReference('work_schedule_profile_' . random_int(0, 4));
        /* @var $defaultWorkScheduleProfileREF WorkScheduleProfile */

        $departmentREF = $this->fixtures->getReference('department_' . random_int(0, 19));
        /* @var $departmentREF Department */

        $payload = <<<JSON
{
    "samAccountName": "user_test_put2",
    "username": "user_test_put2",
    "email": "user_test_put2@example.net",
    "firstName": "User",
    "lastName": "Test",
    "roles": [
        "ROLE_USER"
    ],
    "distinguishedName": null,
    "title": "Pan Test",
    "plainPassword": "test",
    "section": null,
    "department": "/api/departments/{$departmentREF->getId()}",
    "defaultWorkScheduleProfile": "/api/work_schedule_profiles/{$defaultWorkScheduleProfileREF->getId()}",
    "managedDepartments": ["/api/departments/{$departmentREF->getId()}"]
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_PUT,
            '/api/users/' . $userREF->getId(),
            $payload,
            [],
            200,
            self::REF_ADMIN
        );

        $userJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userJSON);
        $this->assertIsNumeric($userJSON->id);
        $this->assertEquals('user_test_put2', $userJSON->username);
        $this->assertEquals('user_test_put2', $userJSON->samAccountName);
        $this->assertEquals('user_test_put2@example.net', $userJSON->email);
        $this->assertEquals('User', $userJSON->firstName);
        $this->assertEquals('Test', $userJSON->lastName);
        $this->assertEquals(['ROLE_USER'], $userJSON->roles);
        $this->assertEquals('Pan Test', $userJSON->title);

        $response = $this->getActionResponse(
            'GET',
            '/api/users/' . $userREF->getId()
        );

        $userDB = $this->entityManager->getRepository(User::class)->find($userREF->getId());
        /* @var $userDB User */

        $userJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userJSON);
        $this->assertEquals($userDB->getId(), $userJSON->id);
        $this->assertEquals($userDB->getUsername(), $userJSON->username);
        $this->assertEquals($userDB->getSamAccountName(), $userJSON->samAccountName);
        $this->assertEquals($userDB->getEmail(), $userJSON->email);
        $this->assertEquals($userDB->getFirstName(), $userJSON->firstName);
        $this->assertEquals($userDB->getLastName(), $userJSON->lastName);
        $this->assertEquals($userDB->getRoles(), $userJSON->roles);
        $this->assertEquals($userDB->getTitle(), $userJSON->title);
        $this->assertEquals($userDB->getDepartment()->getId(), $userJSON->department->id);
        $this->assertEquals(
            $userDB->getDefaultWorkScheduleProfile()->getId(),
            $userJSON->defaultWorkScheduleProfile->id
        );

        $this->assertListContainsSameObjectWithValue(
            $userDB->getManagedDepartments(),
            'getId',
            $departmentREF->getId()
        );
    }
}
