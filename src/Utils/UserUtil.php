<?php

declare(strict_types=1);

namespace App\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;

/**
 * Class UserUtil
 * Simplified fetching current user than directly injecting TokenStorageInterface.
 */
class UserUtil implements UserUtilsInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->token = $tokenStorage->getToken();
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentUser(bool $refreshFromDb = true): ?User
    {
        $token = $this->token;
        if ($token !== null) {
            if (!$refreshFromDb) {
                return $token->getUser();
            }

            return $this
                ->entityManager
                ->getRepository(User::class)
                ->findOneBy([
                    'username' => $token->getUser()->getUsername()
                ])
            ;
        }

        return null;
    }
}
