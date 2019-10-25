<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Utils\UserUtilsInterface;

/**
 * Class UserUtil
 * Some classes use App\Utils\UserUtil to fetch current user.
 * This class is defined as App\Utils\UserUtilsInterface.
 */
class UserUtil implements UserUtilsInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private const DEFAULT_USER = 'admin';

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Test equivalent of the App\Utils\UserUtil class.
     *
     * @param bool $refreshFromDb - just interface inheritance
     *
     * @return null|User
     *
     * @SuppressWarnings("unused")
     */
    public function getCurrentUser(bool $refreshFromDb = true): ?User
    {
        return $this
            ->entityManager
            ->getRepository(User::class)
            ->findOneBy([
                'username' => self::DEFAULT_USER,
            ])
        ;
    }
}
