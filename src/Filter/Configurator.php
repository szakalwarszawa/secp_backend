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
        if ($user = $this->getUser()) {
            $filter = $this->entityManager->getFilters()->enable('user_filter');
            $filter
                ->setParameter('id', $user->getId())
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
