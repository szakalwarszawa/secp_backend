<?php

namespace App\EventListener;

use App\Entity\DayDefinition;
use App\Entity\DayDefinitionLog;
use App\Entity\User;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetDay;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use App\Entity\WorkScheduleProfile;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
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
    private $userWorkScheduleDays = [];

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
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof UserWorkSchedule) {
            return;
        }

        if ($args->hasChangedField('status') && $args->getOldValue('status') !== $args->getNewValue('status')) {
            $this->addUserWorkScheduleLog(
                $args,
                $entity,
                sprintf(
                    "Zmieniono status z:\n%s\nna:\n%s",
                    $args->getOldValue('status'),
                    $args->getNewValue('status')
                )
            );
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     * @param UserWorkSchedule $entity
     * @param string $notice
     */
    private function addUserWorkScheduleLog(PreUpdateEventArgs $args, UserWorkSchedule $entity, string $notice): void
    {
        return; // @Todo Do dodania tabela z logami zmian i dodawanie do niej danych
        $log = new DayDefinitionLog();
        $log->setDayDefinition($entity);
        $log->setLogDate(date('Y-m-d H:i:s'));
        $log->setOwner($this->getCurrentUser($args->getEntityManager()));
        $log->setNotice($notice);

        $this->userWorkScheduleDays[] = $log;
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

        // @Todo dodać tabele z logami i dopisywać zmiany
    }

    /**
     * @param LifecycleEventArgs $args
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

        // @Todo Dodać sprawdzanie czy w zadanym okresie czasu są definicje dni [DayDefinition] jeśli nie ma to albo exception albo dodawać definicje

        foreach ($dayDefinitions as $dayDefinition) {
            $userWorkScheduleProfile = $userWorkSchedule->getWorkScheduleProfile();

            $this->userWorkScheduleDays[] = $this->addUserScheduleDays(
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
     * @param WorkScheduleProfile|null $userWorkScheduleProfile
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
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        if (!empty($this->userWorkScheduleDays)) {
            $em = $args->getEntityManager();

            foreach ($this->userWorkScheduleDays as $userWorkScheduleDay) {
                $userWorkScheduleDay->getUserWorkSchedule()->addUserWorkScheduleDay($userWorkScheduleDay);
                $em->persist($userWorkScheduleDay);
            }

            $this->userWorkScheduleDays = [];
            $em->flush();
        }
    }
}
