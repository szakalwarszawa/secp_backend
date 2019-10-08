<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use App\Entity\DayDefinition;
use App\Entity\UserWorkScheduleLog;
use App\Validator\Rules\StatusChangeDecision;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\OptimisticLockException;
use DateTime;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Exception\IncorrectStatusChangeException;

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
     * @var StatusChangeDecision
     */
    private $statusChangeDecision;

    /**
     * UserWorkScheduleListener constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param StatusChangeDecision $statusChangeDecision
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        StatusChangeDecision $statusChangeDecision
    ) {
        $this->token = $tokenStorage->getToken();
        $this->statusChangeDecision = $statusChangeDecision;
    }

    /**
     * @param PreUpdateEventArgs $args
     *
     * @throws IncorrectStatusChangeException by StatusChangeDecision::class
     *
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
            $this
                ->statusChangeDecision
                ->setThrowException(true)
                ->decide(
                    $args->getOldValue('status'),
                    $args->getNewValue('status')
                )
            ;

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
            && $args->getNewValue('status')->getId() === UserWorkSchedule::STATUS_HR_ACCEPT
        ) {
            $args->getEntityManager()->getRepository(UserWorkSchedule::class)
                ->markPreviousScheduleDaysNotActive($currentSchedule);
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
        $log->setUserWorkSchedule($entity)
            ->setLogDate(new DateTime())
            ->setOwner($this->getCurrentUser($args->getEntityManager()))
            ->setNotice($notice)
        ;

        $this->userWorkScheduleDaysLogs[] = $log;
    }

    /**
     * @param EntityManager $entityManager
     * @return User|null
     */
    private function getCurrentUser(EntityManager $entityManager): ?User
    {
        /* @var User $user */
        $userName = null === $this->token ? 'admin' : $this->token->getUser()->getUsername();

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
     *
     * @return void
     *
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
            $this->userWorkScheduleDays[] = $this->addUserScheduleDays(
                $args->getEntityManager(),
                $dayDefinition,
                $userWorkSchedule
            );
        }
    }

    /**
     * @param EntityManager $entityManager
     * @param DayDefinition $dayDefinition
     * @param UserWorkSchedule $userWorkSchedule
     *
     * @return UserWorkScheduleDay
     *
     * @throws ORMException
     */
    private function addUserScheduleDays(
        EntityManager $entityManager,
        DayDefinition $dayDefinition,
        UserWorkSchedule $userWorkSchedule
    ): UserWorkScheduleDay {
        $userWorkScheduleOwner = $userWorkSchedule->getOwner();
        $userWorkScheduleDay = new UserWorkScheduleDay();
        $userWorkScheduleDay
            ->setDayDefinition($dayDefinition)
            ->setDailyWorkingTime((float) $userWorkScheduleOwner->getDailyWorkingTime())
            ->setWorkingDay($dayDefinition->getWorkingDay())
            ->setDayStartTimeFrom($userWorkScheduleOwner->getDayStartTimeFrom())
            ->setDayStartTimeTo($userWorkScheduleOwner->getDayStartTimeTo())
            ->setDayEndTimeFrom($userWorkScheduleOwner->getDayEndTimeFrom())
            ->setDayEndTimeTo($userWorkScheduleOwner->getDayEndTimeTo())
            ->setActive($userWorkSchedule->getStatus()->getId() === UserWorkSchedule::STATUS_HR_ACCEPT)
            ;

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
