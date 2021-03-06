<?php

declare(strict_types=1);

namespace App\Filter;

use App\Entity\User;
use App\Filter\Query\AccessLevel;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class Configurator
 */
class Configurator
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var Security
     */
    private $security;

    /**
     * Configurator constructor.
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     * @param Security $security
     * @param Reader $reader
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        Security $security,
        Reader $reader
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->security = $security;
        $this->reader = $reader;
    }

    /**
     * onKernelRequest event service
     */
    public function onKernelRequest(): void
    {
        if ($this->getUser()) {
            $filter = $this->entityManager->getFilters()->enable('user_filter');
            // @todo check for better solution to manage filter cache
            $filter
                ->setParameter('cache', bin2hex(openssl_random_pseudo_bytes(20)))
                ->setAccessLevel(new AccessLevel($this->getUser(), $this->security))
                ->setAnnotationReader($this->reader)
            ;
        }
    }

    /**
     * @return User|null
     */
    private function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
