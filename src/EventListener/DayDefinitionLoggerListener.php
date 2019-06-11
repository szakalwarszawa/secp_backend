<?php

namespace App\EventListener;

use App\Entity\DayDefinition;
use App\Entity\DayDefinitionLog;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DayDefinitionLoggerListener
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TokenInterface|null
     */
    private $token;

    /**
     * @var DayDefinitionLog[]
     */
    private $dayDefinitionLogs = [];

    /**
     * DayDefinitionLoggerListener constructor.
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->token = $tokenStorage->getToken();
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof DayDefinition) {
            return;
        }

        $log = new DayDefinitionLog();
        $log->setDayDefinition($entity);
        $log->setLogDate(date('Y-m-d H:i:s'));
        $log->setOwner($this->getCurrentUser());
        $log->setNotice('');

        $this->dayDefinitionLogs[] = $log;
    }

    /**
     * @return User|null
     */
    private function getCurrentUser(): ?User
    {
        /* @var User $user */
        if (null === $this->token) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'admin']);
        } else {
            $user = $this->token->getUser();
        }

        return $user;
    }

    /**
     * @param PostFlushEventArgs $args
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        if (!empty($this->dayDefinitionLogs)) {
            $em = $args->getEntityManager();

            foreach ($this->dayDefinitionLogs as $log) {
                $em->persist($log);
            }

            $this->dayDefinitionLogs = [];
            $em->flush();
        }
    }
}
