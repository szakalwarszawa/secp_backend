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
     * Get current user object.
     *
     * @param bool $refreshFromDb - if true, user object will be fetched from database.
     *
     * @return User|null
     */
    public function getCurrentUser(bool $refreshFromDb = true): ?User;
}
