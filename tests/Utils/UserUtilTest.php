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

        /**
         * UserUtil is an PROD instance of UserUtilsInterface.
         * Depends on environment UserUtilsInterface is:
         *  - Test: App\Tests\UserUtil
         *  - DEV/PROD: App\Utils\UserUtil
         *
         * This time DEV/PROD is called explicitly. Due to services configuration,
         * calling it in this way is only possible in test env.
         * (services_test.yaml UserUtil is defined as public)
         */
        $currentUserShouldBeLogged = self::$container->get(UserUtil::class)->getCurrentUser();
        $this->assertInstanceOf(User::class, $currentUserShouldBeLogged);
    }
}
