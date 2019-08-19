<?php


namespace App\Filter;

use App\Entity\User;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class Configurator
 * @package App\Filter
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
     * @param EntityManager $entityManager
     * @param TokenStorageInterface $tokenStorage
     * @param Reader $reader
     */
    public function __construct(EntityManager $entityManager, TokenStorageInterface $tokenStorage, Reader $reader)
    {

        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->reader = $reader;
    }

    /**
     *
     */
    public function onKernelRequest(): void
    {
        if ($user = $this->getUser()) {
            $filter = $this->entityManager->getFilters()->enable('user_filter');
            $filter->setParameter('id', $user->getId());
            $filter->setAnnotationReader($this->reader);
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

        if (!($user instanceof User)) {
            return null;
        }

        return $user;
    }
}
