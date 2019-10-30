<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\User;

/**
 * UserUtilsInterface
 * Helpful in tests where user is not stored in token storage.
 * Defined in services.yaml & services_test.yaml
 */
interface UserUtilsInterface
{
    /**
     * In anonymous path TokenInterface:getUser() is not User object, it is string 'anon.'
     *
     * @var string
     */
    public const ANONYMOUS_USER = 'anon.';

    /**
     * Get current user object.
     *
     * @param bool $refreshFromDb - if true, user object will be fetched from database.
     *
     * @return User|null
     */
    public function getCurrentUser(bool $refreshFromDb = true): ?User;
}
