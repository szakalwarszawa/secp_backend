<?php


namespace App\Tests\Api;

use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;

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
}
