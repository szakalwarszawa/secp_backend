<?php

namespace App\EventListener;

use App\Entity\DayDefinition;
use App\Entity\DayDefinitionLog;
use App\Entity\User;
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
     * DayDefinitionLoggerListener constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->token = $tokenStorage->getToken();
    }

    /**
     * @param $first
     * @param $second
     * @return false|int
     */
    public function compareScheduleDays($first, $second)
    {
        return strtotime($first['id']) - strtotime($second['id']);
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

        if ($args->hasChangedField('status') && $args->getOldValue('status') !== $args->getNewValue('status')) {
            $this->addUserWorkScheduleLog(
                $args,
                $currentSchedule,
                sprintf(
                    "Zmieniono status z:\n%s\nna:\n%s",
                    $args->getOldValue('status'),
                    $args->getNewValue('status')
                )
            );
        }

        if ($args->hasChangedField('status') && $args->getNewValue('status')
            === UserWorkSchedule::STATUS_HR_ACCEPT) {
            $idOwnerCurrent = $currentSchedule->getOwner()->getId();
            $idScheduleProfileCurrent = $currentSchedule->getWorkScheduleProfile()->getId();

            $previousWorkScheduleToDelete = $args->getEntityManager()
                ->getRepository(UserWorkSchedule::class)
                ->createQueryBuilder('p')
                ->andWhere('p.owner = :owner')
                ->andWhere('p.workScheduleProfile = :profile')
                ->addOrderBy('p.id', 'asc')
                ->setParameter('owner', $idOwnerCurrent)
                ->setParameter('profile', $idScheduleProfileCurrent)
                ->getQuery()
                ->getResult();

            $daysCurrentCompare = array();
            $daysPreviousCompare = array();

            $previous = $previousWorkScheduleToDelete[0];
            $userWorkSchedulePrevious = $previous->getId();
            $todayNumeric = date('Y-m-d');

            $daysCurrent = $currentSchedule->getUserWorkScheduleDays();
            foreach ($daysCurrent as $day) {
                if ($day->getDayDefinition()->getId() > strtotime($todayNumeric)) {
                    $daysCurrentCompare[]["id"] = $day->getDayDefinition()->getId();
                }
            }

            $daysPrevious = $previous->getUserWorkScheduleDays();
            foreach ($daysPrevious as $day) {
                $daysPreviousCompare[]["id"] = $day->getDayDefinition()->getId();
            }

            $toDeleteDays = array_udiff($daysPreviousCompare, $daysCurrentCompare, array($this, 'compareScheduleDays'));

            foreach ($toDeleteDays as $day) {
                if (strtotime($day['id']) > strtotime($todayNumeric)) {
                    $val = false;
                    $deleteQuery = $args->getEntityManager()->createQueryBuilder('p');
                    $deleteQuery->update('App\Entity\UserWorkScheduleDay', 'p')
                        ->set('p.visibility', ':visibility')
                        ->where('p.dayDefinition = :delete')
                        ->andWhere('p.userWorkSchedule = :previous')
                        ->setParameter('delete', $day['id'])
                        ->setParameter('previous', $userWorkSchedulePrevious)
                        ->setParameter('visibility', $val);
                    $deleteQuery->getQuery()->execute();
                }
            }
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

        // @Todo dodać tabele z logami i dopisywać zmiany
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
            ->setVisibility(true);

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
    }
}
