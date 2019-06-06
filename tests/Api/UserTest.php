<?php


namespace App\Tests\Api;

use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;

class UserTest extends AbstractWebTestCase
{
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
            $randomUser = random_int(0, 100);
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
        $payload = [
            [
                "id" => 836,
                "samAccountName" => "wasilewski_kamil",
                "username" => "wasilewski_kamil",
                "email" => "wasilewski_kamil@example.net",
                "firstName" => "Kamil",
                "lastName" => "Wasilewski",
                "roles" => [
                    'ROLE_USER'
                ],
                "distinguishedName" => null,
                "title" => "Irytowało mnie to szydercze porozumienie, ta.",
                "department" => 220,
                "section" => 317,
            ],
        ];

        $response = $this->getActionResponse(self::HTTP_POST, '/api/users', $payload, 201, self::REF_ADMIN);
//        $response = $client->request(self::HTTP_POST, '/api/users/', $payload);


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
}
