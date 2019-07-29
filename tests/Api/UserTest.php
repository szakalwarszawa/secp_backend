<?php


namespace App\Tests\Api;

use App\Entity\Department;
use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;

class UserTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetSections(): void
    {
        $usersDB = $this->entityManager->getRepository(User::class)->findAll();
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
        /* @var $userDB \App\Entity\User */

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
    }

    /**
     * @return array
     * @throws \Exception
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
        $departmentRef = $this->fixtures->getReference('department_0');
        /* @var $departmentRef Department */

        $id = $departmentRef->getId();

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
    "department": "/api/departments/{$departmentRef->getId()}"
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
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     * @throws \Exception
     */
    public function apiPutUser(): void
    {
        $userREF = $this->fixtures->getReference('user_' . random_int(0, 99));
        /* @var $userREF User */

        $departmentRef = $this->fixtures->getReference('department_admin');
        /* @var $departmentRef Department */

        $id = $departmentRef->getId();

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
     * @throws \Exception
     */
    public function apiPutUserWithManagedDepartment(): void
    {
        $userREF = $this->fixtures->getReference('user_' . random_int(0, 99));
        /* @var $userREF User */

        $departmentREF = $this->fixtures->getReference('department_' . random_int(0, 19));
        /* @var $departmentREF Department */

        $id = $departmentREF->getId();

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

        $this->assertListContainsSameObjectWithValue(
            $userDB->getManagedDepartments(),
            'getId',
            $departmentREF->getId()
        );
    }
}
