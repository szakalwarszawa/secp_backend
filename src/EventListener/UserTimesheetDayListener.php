<?php

namespace App\EventListener;

use App\Entity\DayDefinition;
use App\Entity\UserTimesheetDayLog;
use App\Entity\User;
use App\Entity\UserTimesheet;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use App\Entity\UserTimesheetDay;
use DateTime;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use App\Entity\WorkScheduleProfile;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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

        if ($args->hasChangedField('presenceType') &&
            $args->getOldValue('presenceType') !== $args->getNewValue('presenceType')) {
            $this->addUserTimeSheetDayLog(
                $args,
                $entity,
                sprintf(
                    "Zmieniono typ obecności z: %s na: %s",
                    $args->getOldValue('presenceType')->getName(),
                    $args->getNewValue('presenceType')->getName()
                )
            );
        }

        if ($args->hasChangedField('absenceType') && $args->getOldValue('absenceType') !==
            $args->getNewValue('absenceType')) {
            $this->addUserTimeSheetDayLog(
                $args,
                $entity,
                sprintf(
                    "Zmieniono typ nieobecności z: %s na: %s",
                    $args->getOldValue('absenceType')->getName(),
                    $args->getNewValue('absenceType')->getName()
                )
            );
        }

        if ($args->hasChangedField('dayStartTime') && $args->getOldValue('dayStartTime') !==
            $args->getNewValue('dayStartTime')) {
            $this->addUserTimeSheetDayLog(
                $args,
                $entity,
                sprintf(
                    "Zmieniono rozpoczęcie dnia z: %s na: %s",
                    $args->getOldValue('dayStartTime'),
                    $args->getNewValue('dayStartTime')
                )
            );
        }

        if ($args->hasChangedField('dayEndTime') && $args->getOldValue('dayEndTime') !==
            $args->getNewValue('dayEndTime')) {
            $this->addUserTimeSheetDayLog(
                $args,
                $entity,
                sprintf(
                    "Zmieniono zakończenie dnia z: %s na: %s",
                    $args->getOldValue('dayEndTime'),
                    $args->getNewValue('dayEndTime')
                )
            );
        }

        if ($args->hasChangedField('workingTime') && $args->getOldValue('workingTime') !==
            $args->getNewValue('workingTime')) {
            $this->addUserTimeSheetDayLog(
                $args,
                $entity,
                sprintf(
                    "Zmieniono czas pracy z: %s na: %s",
                    $args->getOldValue('workingTime'),
                    $args->getNewValue('workingTime')
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
        $log->setUserTimesheetDay($entity);
        $log->setLogDate(new DateTime());
        $log->setOwner($this->getCurrentUser($args->getEntityManager()));
        $log->setNotice($notice);

        $this->userTimesheetDaysLogs[] = $log;
    }

    /**
     * @param EntityManager $entityManager
     * @return User|null
     */
    private function getCurrentUser(EntityManager $entityManager): ?User
    {
        /* @var User $user */
        if (null === $this->token) {
            $userName = 'user';
        } else {
            $userName = $this->token->getUser()->getUsername();
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $userName]);

        return $user;
    }

    /**
     * @param LifecycleEventArgs $args
     * @return void
     * @throws \RuntimeException
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
            throw new \RuntimeException('Missing user work schedule day or user schedule is not defined');
        }

        $userTimesheetDay->setUserWorkScheduleDay($userWorkScheduleDay);

        if ($userWorkScheduleDay->getDayDefinition() === null) {
            throw new \RuntimeException('Missing user work schedule day');
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
     * @param LifecycleEventArgs $args
     * @return void
     * @throws ORMException
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $userWorkSchedule = $args->getObject();
        /* @var $userWorkSchedule UserWorkSchedule */
        if (!$userWorkSchedule instanceof UserWorkSchedule) {
            return;
        }

        $dayDefinitions = $args->getEntityManager()->getRepository(DayDefinition::class)
            ->findAllBetweenDate(
                $userWorkSchedule->getFromDate()->format('Y-m-d'),
                $userWorkSchedule->getToDate()->format('Y-m-d')
            );
        /* @var $dayDefinitions DayDefinition[] */

        // @Todo Dodać sprawdzanie czy w zadanym okresie czasu są definicje dni [DayDefinition]
        // jeśli nie ma to albo exception albo dodawać definicje

        foreach ($dayDefinitions as $dayDefinition) {
            $userWorkScheduleProfile = $userWorkSchedule->getWorkScheduleProfile();

            $this->userTimesheetDaysLogs[] = $this->addUserScheduleDays(
                $args->getEntityManager(),
                $dayDefinition,
                $userWorkSchedule,
                $userWorkScheduleProfile
            );
        }
    }

    /**
     * @param EntityManager $entityManager
     * @param DayDefinition $dayDefinition
     * @param UserWorkSchedule $userWorkSchedule
     * @param WorkScheduleProfile $userWorkScheduleProfile
     * @return UserWorkScheduleDay
     * @throws ORMException
     */
    private function addUserScheduleDays(
        EntityManager $entityManager,
        DayDefinition $dayDefinition,
        UserWorkSchedule $userWorkSchedule,
        WorkScheduleProfile $userWorkScheduleProfile
    ): UserWorkScheduleDay {
        $userWorkScheduleDay = new UserWorkScheduleDay();
        $userWorkScheduleDay->setDayDefinition($dayDefinition)
            ->setDailyWorkingTime($userWorkScheduleProfile->getDailyWorkingTime())
            ->setWorkingDay($dayDefinition->getWorkingDay())
            ->setDayStartTimeFrom($userWorkScheduleProfile->getDayStartTimeFrom())
            ->setDayStartTimeTo($userWorkScheduleProfile->getDayStartTimeTo())
            ->setDayEndTimeFrom($userWorkScheduleProfile->getDayStartTimeFrom())
            ->setDayEndTimeTo($userWorkScheduleProfile->getDayStartTimeTo());

        $userWorkSchedule->addUserWorkScheduleDay($userWorkScheduleDay);
        $entityManager->persist($userWorkScheduleDay);
        return $userWorkScheduleDay;
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
