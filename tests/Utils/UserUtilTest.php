<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use App\Utils\UserUtil;

/**
 * Class UserUtilTest
 */
class UserUtilTest extends AbstractWebTestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function testGetCurrentUserEmptyTokenStorage(): void
    {
        $currentUserShouldBeNull = self::$container->get(UserUtil::class)->getCurrentUser();
        $this->assertNull($currentUserShouldBeNull);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testGetCurrentUserAfterLogin(): void
    {
        $this->assertNull($this->tokenStorage);
        $user = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        /**
         * Login as ROLE_ADMIN
         */
        $this->loginAsUser($user, ['ROLE_ADMIN']);

        $currentUserShouldBeLogged = self::$container->get(UserUtil::class)->getCurrentUser();
        $this->assertInstanceOf(User::class, $currentUserShouldBeLogged);
    }
}
