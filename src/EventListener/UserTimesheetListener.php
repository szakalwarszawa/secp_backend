<?php

namespace App\EventListener;

use App\Entity\UserTimesheetLog;
use App\Entity\User;
use App\Entity\UserTimesheet;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class UserTimesheetLoggerListener
 * @package App\EventListener
 */
class UserTimesheetListener
{
    /**
     * @var TokenInterface|null
     */
    private $token;

    /**
     * @var UserTimesheetLog[]
     */
    private $userTimesheetLogs = [];

    /**
     * UserTimesheetListener constructor.
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
        if (!$entity instanceof UserTimesheet) {
            return;
        }

        if ($args->hasChangedField('status')
            && $args->getOldValue('status') !== $args->getNewValue('status')
        ) {
            $this->addUserTimesheetLog(
                $args,
                $entity,
                sprintf(
                    'Zmieniono status z: %s na: %s',
                    $args->getOldValue('status'),
                    $args->getNewValue('status')
                )
            );
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     * @param UserTimesheet $entity
     * @param string $notice
     */
    private function addUserTimesheetLog(PreUpdateEventArgs $args, UserTimesheet $entity, string $notice): void
    {
        $log = new UserTimesheetLog();
        $log->setUserTimesheet($entity);
        $log->setLogDate(date('Y-m-d H:i:s'));
        $log->setOwner($this->getCurrentUser($args->getEntityManager()));
        $log->setNotice($notice);

        $this->userTimesheetLogs[] = $log;
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
        if (!empty($this->userTimesheetLogs)) {
            $em = $args->getEntityManager();

            foreach ($this->userTimesheetLogs as $log) {
                $em->persist($log);
            }

            $this->userTimesheetLogs = [];
            $em->flush();
        }
    }
}
