<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use App\Entity\DayDefinition;
use App\Entity\UserWorkScheduleLog;
use App\Entity\UserWorkScheduleStatus;
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
 * Class UserWorkScheduleListener
 * @package App\EventListener
 */
class UserWorkScheduleListener
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
     * @var array
     */
    private $userWorkScheduleDaysLogs = [];

    /**
     * UserWorkScheduleListener constructor.
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
        $currentSchedule = $args->getObject();
        if (!$currentSchedule instanceof UserWorkSchedule) {
            return;
        }

        if ($args->hasChangedField('status')
            && $args->getOldValue('status') !== $args->getNewValue('status')
        ) {
            $this->addUserWorkScheduleLog(
                $args,
                $currentSchedule,
                sprintf(
                    'Zmieniono status z: %s, na: %s',
                    $args->getOldValue('status')->getId(),
                    $args->getNewValue('status')->getId()
                )
            );
        }

        if ($args->hasChangedField('status')
            && $args->getNewValue('status')->getId() === UserWorkScheduleStatus::STATUS_HR_ACCEPT
        ) {
//            $args->getEntityManager()
//                ->createQueryBuilder()
//                ->update(UserWorkScheduleDay::class, 'p')
//                ->set('p.visibility', ':setVisibility')
//                ->setParameter('setVisibility', false)
//                ->where('p.dayDefinition <= :todayDate')
//                ->setParameter('todayDate', date('Y-m-d'))
//                ->andWhere('p.userWorkSchedule = :userWorkSchedule')
//                ->setParameter('userWorkSchedule', $currentSchedule)
//                ->andWhere('p.visibility = :previousVisible')
//                ->setParameter('previousVisible', true)
//                ->getQuery()
//                ->execute();
//
            $args->getEntityManager()
                ->createQueryBuilder()
                ->update(UserWorkScheduleDay::class, 'p')
                ->set('p.visibility', ':setVisibility')
                ->setParameter('setVisibility', false)
                ->andWhere('p.dayDefinition >= :tomorrowDate')
                ->setParameter('tomorrowDate', date('Y-m-d', strtotime('now +1 days')))
                ->andWhere('p.userWorkSchedule != :userWorkSchedule')
                ->setParameter('userWorkSchedule', $currentSchedule)
                ->andWhere('p.dayDefinition BETWEEN :fromDate AND :toDate')
                ->setParameter('fromDate', $currentSchedule->getFromDate()->format('Y-m-d'))
                ->setParameter('toDate', $currentSchedule->getToDate()->format('Y-m-d'))
                ->andWhere('p.visibility = :previousVisible')
                ->setParameter('previousVisible', true)
                ->getQuery()
                ->execute();

            $args->getEntityManager()
                ->createQueryBuilder()
                ->update(UserWorkScheduleDay::class, 'p')
                ->set('p.visibility', ':setVisibility')
                ->setParameter('setVisibility', true)
                ->where('p.dayDefinition >= :tomorrowDate')
                ->setParameter('tomorrowDate', date('Y-m-d', strtotime('now +1 days')))
                ->andWhere('p.userWorkSchedule = :userWorkSchedule')
                ->setParameter('userWorkSchedule', $currentSchedule)
                ->getQuery()
                ->execute();
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     * @param UserWorkSchedule $entity
     * @param string $notice
     * @return void
     */
    private function addUserWorkScheduleLog(PreUpdateEventArgs $args, UserWorkSchedule $entity, string $notice): void
    {
        $log = new UserWorkScheduleLog();
        $log->setUserWorkSchedule($entity);
        $log->setLogDate(date('Y-m-d H:i:s'));
        $log->setOwner($this->getCurrentUser($args->getEntityManager()));
        $log->setNotice($notice);

        $this->userWorkScheduleDaysLogs[] = $log;
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
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $userWorkSchedule = $args->getObject();
        /* @var $userWorkSchedule UserWorkSchedule */
        if (!$userWorkSchedule instanceof UserWorkSchedule) {
            return;
        }
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
            ->setDayEndTimeFrom($userWorkScheduleProfile->getDayEndTimeFrom())
            ->setDayEndTimeTo($userWorkScheduleProfile->getDayEndTimeTo())
            ->setVisibility($userWorkSchedule->getStatus()->getId() === UserWorkScheduleStatus::STATUS_HR_ACCEPT);

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
        if (!empty($this->userWorkScheduleDays)) {
            $em = $args->getEntityManager();

            foreach ($this->userWorkScheduleDays as $userWorkScheduleDay) {
                $userWorkScheduleDay->getUserWorkSchedule()->addUserWorkScheduleDay($userWorkScheduleDay);
                $em->persist($userWorkScheduleDay);
            }

            $this->userWorkScheduleDays = [];
            $em->flush();
        }

        if (!empty($this->userWorkScheduleDaysLogs)) {
            $em = $args->getEntityManager();

            foreach ($this->userWorkScheduleDaysLogs as $log) {
                $em->persist($log);
            }

            $this->userWorkScheduleDaysLogs = [];
            $em->flush();
        }
    }
}
