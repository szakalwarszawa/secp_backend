<?php

namespace App\EventListener;

use App\Entity\DayDefinition;
use App\Entity\DayDefinitionLog;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class DayDefinitionLoggerListener
 * @package App\EventListener
 */
class DayDefinitionLoggerListener
{
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
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->token = $tokenStorage->getToken();
    }

    /**
     * @param PreUpdateEventArgs $args
     * @return void
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof DayDefinition) {
            return;
        }

        if ($args->hasChangedField('workingDay')
            && $args->getOldValue('workingDay') !== $args->getNewValue('workingDay')
        ) {
            $this->addDayDefinitionLog(
                $args,
                $entity,
                $args->getNewValue('workingDay')
                    ? 'Dzień został ustawiony jako pracujący'
                    : 'Dzień został ustawiony jako niepracujący'
            );
        }

        if ($args->hasChangedField('notice') && $args->getOldValue('notice') !== $args->getNewValue('notice')) {
            $this->addDayDefinitionLog(
                $args,
                $entity,
                sprintf(
                    "Zmieniono opis z:\n%s\nna:\n%s",
                    $args->getOldValue('notice'),
                    $args->getNewValue('notice')
                )
            );
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     * @param DayDefinition $entity
     * @param string $notice
     * @return void
     */
    private function addDayDefinitionLog(PreUpdateEventArgs $args, DayDefinition $entity, string $notice): void
    {
        $log = new DayDefinitionLog();
        $log->setDayDefinition($entity);
        $log->setLogDate(new DateTime());
        $log->setOwner($this->getCurrentUser($args->getEntityManager()));
        $log->setNotice($notice);

        $this->dayDefinitionLogs[] = $log;
    }

    /**
     * @param EntityManager $entityManager
     * @return User|null
     */
    private function getCurrentUser(EntityManager $entityManager): ?User
    {
        /* @var User $user */
        if (null === $this->token) {
            $userName = 'admin';
        } else {
            $userName = $this->token->getUser()->getUsername();
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $userName]);

        return $user;
    }

    /**
     * @param PostFlushEventArgs $args
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        if (!empty($this->dayDefinitionLogs)) {
            $em = $args->getEntityManager();

            foreach ($this->dayDefinitionLogs as $log) {
                $log->getDayDefinition()->addDayDefinitionLog($log);
                $em->persist($log);
            }

            $this->dayDefinitionLogs = [];
            $em->flush();
        }
    }
}
