<?php


namespace App\Tests\Api;

use App\DataFixtures\UserFixtures;
use App\Entity\Department;
use App\Entity\User;
use App\Entity\WorkScheduleProfile;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use Doctrine\Tests\Common\DataFixtures\TestFixtures\UserFixture;
use Exception;

class UserTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetUsers(): void
    {
        /* @var $userDB User */
        $usersDB = $this->entityManager->getRepository(User::class)->findAll();
        $response = $this->getActionResponse('GET', '/api/users');
        $usersJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($usersJSON);
        $this->assertEquals(count($usersDB), $usersJSON->{'hydra:totalItems'});

    }

    /**
     * @test
     * @dataProvider apiGetUserProvider
     * @param string $referenceName
     * @throws NotFoundReferencedUserException
     */
    public function apiGetUser($referenceName): void
    {
        $userDB = $this->fixtures->getReference($referenceName);
        /* @var $userDB User */

        $response = $this->getActionResponse('GET', '/api/users/' . $userDB->getId());
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
    }

    /**
     * @return array
     * @throws Exception
     */
    public function apiGetUserProvider(): array
    {
        $referenceList = [];

        for ($i = 0; $i < 20; $i++) {
            $randomUser = random_int(0, 99);
            $referenceList[] = ['user_' . $randomUser];
        }

        return $referenceList;
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiPostUser(): void
    {
        $workScheduleProfileRef = $this->fixtures->getReference('work_schedule_profile_0');
        /* @var $workScheduleProfileRef WorkScheduleProfile */

        $departmentRef = $this->fixtures->getReference('department_4');
        /* @var $departmentRef Department */

        $payload = <<<JSON
{
    "samAccountName": "user_test_post",
    "username": "user_test_post",
    "email": "user_test_post@example.net",
    "firstName": "User",
    "lastName": "Test",
    "roles": [
        "ROLE_USER"
    ],
    "distinguishedName": null,
    "title": "Pan Test",
    "plainPassword": "test",
    "department": "/api/departments/{$departmentRef->getId()}",
    "defaultWorkScheduleProfile": "/api/work_schedule_profiles/{$workScheduleProfileRef->getId()}"
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_POST,
            '/api/users',
            $payload,
            [],
            201,
            self::REF_ADMIN
        );

        $userJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userJSON);
        $this->assertIsNumeric($userJSON->id);
        $this->assertEquals('user_test_post', $userJSON->username);
        $this->assertEquals('user_test_post', $userJSON->samAccountName);
        $this->assertEquals('user_test_post@example.net', $userJSON->email);
        $this->assertEquals('User', $userJSON->firstName);
        $this->assertEquals('Test', $userJSON->lastName);
        $this->assertEquals(['ROLE_USER'], $userJSON->roles);
        $this->assertEquals('Pan Test', $userJSON->title);

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/users/' . $userJSON->id
        );

        $userDB = $this->entityManager->getRepository(User::class)->find($userJSON->id);
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
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     * @throws Exception
     */
    public function apiPutUser(): void
    {
        $userREF = $this->fixtures->getReference('user_' . random_int(0, 99));
        /* @var $userREF User */

        $departmentRef = $this->fixtures->getReference('department_admin');
        /* @var $departmentRef Department */

        $payload = <<<JSON
{
    "samAccountName": "user_test_put",
    "username": "user_test_put",
    "email": "user_test_put@example.net",
    "firstName": "User",
    "lastName": "Test",
    "roles": [
        "ROLE_USER"
    ],
    "distinguishedName": null,
    "title": "Pan Test",
    "plainPassword": "test",
    "section": null,
    "department": "/api/departments/{$departmentRef->getId()}"
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
        $this->assertEquals('user_test_put', $userJSON->username);
        $this->assertEquals('user_test_put', $userJSON->samAccountName);
        $this->assertEquals('user_test_put@example.net', $userJSON->email);
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

    /**
     * @test
     * @dataProvider apiGetOwnUserDataProvider
     * @param string $referenceName
     * @throws NotFoundReferencedUserException
     */
    public function apiGetOwnUserData($referenceName): void
    {
        $userDB = $this->fixtures->getReference($referenceName);
        /* @var $userDB User */

        $response = $this->getActionResponse(
            'GET',
            '/api/users/me',
            null,
            [],
            200,
            $referenceName
        );
        $this->assertJson($response->getContent());
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
    }

    /**
     * @return array
     * @throws Exception
     */
    public function apiGetOwnUserDataProvider(): array
    {
        $referenceList = [
            [UserFixtures::REF_USER_ADMIN],
            [UserFixtures::REF_USER_MANAGER],
            [UserFixtures::REF_USER_USER],
        ];

        for ($i = 0; $i < 20; $i++) {
            $randomUser = random_int(0, 99);
            $referenceList[] = ['user_' . $randomUser];
        }

        return $referenceList;
    }
}
