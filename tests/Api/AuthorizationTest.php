<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Tests\AbstractWebTestCase;
use Symfony\Component\HttpFoundation;

class AuthorizationTest extends AbstractWebTestCase
{
    public function testApiGetUsersAsUnauthorizedUser(): void
    {
        $client = $this->makeClient();
        $client->request('GET', '/api/users');
        self::assertStatusCode(401, $client);
    }

    /**
     * @throws NotFoundReferencedUserException
     */
    public function testApiGetUsersAsAuthorizedUser(): void
    {
        $this->getActionResponse('GET', '/api/users');
    }

    public function testApiIncorrectLoginUsernamePassword(): void
    {
        $client = $this->makeClient();
        $payload = <<<'JSON'
{
  "username": "adminadmin",
  "password": "passwordpassword"
}
JSON;

        $client->request(
            self::HTTP_POST,
            '/authentication_token',
            [],
            [],
            [
                'HTTP_Accept' => 'application/ld+json',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $object = json_decode($client->getResponse()->getContent());
        $this->assertEquals('Bad credentials.', $object->message);
        $this->assertEquals(401, $object->code);
    }

    public function testApiIncorrectLoginPassword(): void
    {
        $client = $this->makeClient();
        $payload = <<<'JSON'
{
  "username": "admin",
  "password": "passwordpassword"
}
JSON;

        $client->request(
            self::HTTP_POST,
            '/authentication_token',
            [],
            [],
            [
                'HTTP_Accept' => 'application/ld+json',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $object = json_decode($client->getResponse()->getContent());
        $this->assertEquals('Bad credentials.', $object->message);
        $this->assertEquals(401, $object->code);
    }

    public function testApiIncorrectLoginUsername(): void
    {
        $client = $this->makeClient();
        $payload = <<<'JSON'
{
  "username": "adminadmin",
  "password": "test"
}
JSON;

        $client->request(
            self::HTTP_POST,
            '/authentication_token',
            [],
            [],
            [
                'HTTP_Accept' => 'application/ld+json',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $object = json_decode($client->getResponse()->getContent());
        $this->assertEquals('Bad credentials.', $object->message);
        $this->assertEquals(401, $object->code);
    }

    public function testApiCorrectCredentials(): void
    {
        $client = $this->makeClient();
        $payload = <<<'JSON'
{
  "username": "admin",
  "password": "test"
}
JSON;

        $client->request(
            self::HTTP_POST,
            '/authentication_token',
            [],
            [],
            [
                'HTTP_Accept' => 'application/ld+json',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $code = $client->getResponse()->getStatusCode();
        $this->assertEquals(200, $code);
    }
}
