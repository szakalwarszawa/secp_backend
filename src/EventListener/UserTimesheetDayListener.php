<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetDay;
use DateTime;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class UserTimesheetDayListener
 * @package App\EventListener
 */
class UserTimesheetDayListener
{
    /**
     * @var TokenInterface|null
     */
    private $token;

    /**
     * @var UserWorkScheduleDay[]
     */
    private $userTimesheetDaysLogs = [];

    /**
     * UserTimesheetDayLoggerListener constructor.
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
        if (!$entity instanceof UserTimesheetDay) {
            return;
        }

        $this->checkChanges(
            $args,
            'presenceType',
            'Zmieniono typ obecności z: %s na: %s',
            'getName'
        );
        $this->checkChanges(
            $args,
            'absenceType',
            'Zmieniono typ nieobecności z: %s na: %s',
            'getName'
        );
        $this->checkChanges(
            $args,
            'dayStartTime',
            'Zmieniono rozpoczęcie dnia z: %s na: %s'
        );
        $this->checkChanges(
            $args,
            'dayEndTime',
            'Zmieniono zakończenie dnia z: %s na: %s'
        );
        $this->checkChanges(
            $args,
            'workingTime',
            'Zmieniono czas pracy z: %s na: %s'
        );
    }

    private function checkChanges(
        PreUpdateEventArgs $args,
        string $fieldName,
        string $noticeTemplate,
        ?string $methodName = null
    ): void {
        if ($args->hasChangedField($fieldName) && $args->getOldValue($fieldName) !== $args->getNewValue($fieldName)) {
            $oldValue = $methodName === null
                ? $args->getOldValue($fieldName)
                : $args->getOldValue($fieldName)->$methodName();

            $newValue = $methodName === null
                ? $args->getNewValue($fieldName)
                : $args->getNewValue($fieldName)->$methodName();

            $this->addUserTimeSheetDayLog(
                $args,
                $args->getObject(),
                sprintf(
                    $noticeTemplate,
                    $oldValue,
                    $newValue
                )
            );
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     * @param UserTimesheetDay $entity
     * @param string $notice
     * @return void
     */
    private function addUserTimeSheetDayLog(PreUpdateEventArgs $args, UserTimesheetDay $entity, string $notice): void
    {
        $log = new UserTimesheetDayLog();
        $log->setUserTimesheetDay($entity)
            ->setLogDate(new DateTime())
            ->setOwner($this->getCurrentUser($args->getEntityManager()))
            ->setNotice($notice)
        ;

        $this->userTimesheetDaysLogs[] = $log;
    }

    /**
     * @param EntityManager $entityManager
     * @return User|null
     */
    private function getCurrentUser(EntityManager $entityManager): ?User
    {
        /* @var User $user */
        $userName = null === $this->token ? 'user' : $this->token->getUser()->getUsername();

        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $userName]);

        return $user;
    }

    /**
     * @param LifecycleEventArgs $args
     * @return void
     * @throws RuntimeException
     * @throws ORMException
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entityManager = $args->getEntityManager();

        $userTimesheetDay = $args->getObject();
        /* @var $userTimesheetDay UserTimesheetDay */
        if (!$userTimesheetDay instanceof UserTimesheetDay) {
            return;
        }
        if ($userTimesheetDay->getUserTimesheet() !== null) {
            return;
        }

        $userWorkScheduleDay = $entityManager->getRepository(UserWorkScheduleDay::class)
            ->findWorkDay($this->getCurrentUser($entityManager), $userTimesheetDay->getDayDate());
        /* @var $userWorkScheduleDay UserWorkScheduleDay */

        if ($userWorkScheduleDay === null || !$userWorkScheduleDay instanceof UserWorkScheduleDay) {
            throw new RuntimeException('Missing user work schedule day or user schedule is not defined');
        }

        $userTimesheetDay->setUserWorkScheduleDay($userWorkScheduleDay);

        if ($userWorkScheduleDay->getDayDefinition() === null) {
            throw new RuntimeException('Missing user work schedule day');
        }

        $period = date('Y-m', strtotime($userWorkScheduleDay->getDayDefinition()->getId()));

        $userTimesheet = new UserTimesheet();
        $userTimesheet
            ->setStatus(UserTimesheet::STATUS_OWNER_EDIT)
            ->setPeriod($period)
            ->setOwner($this->getCurrentUser($entityManager));

        $entityManager->persist($userTimesheet);
        $entityManager->getUnitOfWork()->computeChangeSet(
            $entityManager->getClassMetadata(UserTimesheet::class),
            $userTimesheet
        );

        $userTimesheetDay->setUserTimesheet($userTimesheet);
    }

    /**
     * @param PostFlushEventArgs $args
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        if (!empty($this->userTimesheetDaysLogs)) {
            $em = $args->getEntityManager();

            foreach ($this->userTimesheetDaysLogs as $log) {
                $log->getUserTimesheetDay()->addUserTimesheetDayLog($log);
                $em->persist($log);
            }

            $this->userTimesheetDaysLogs = [];
            $em->flush();
        }
    }
}
