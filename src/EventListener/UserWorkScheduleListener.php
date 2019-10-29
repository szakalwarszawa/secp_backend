<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use App\Entity\DayDefinition;
use App\Validator\Rules\StatusChangeDecision;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use App\Exception\IncorrectStatusChangeException;

/**
 * Class UserWorkScheduleListener
 */
class UserWorkScheduleListener
{
    /**
     * @var UserWorkScheduleDay[]
     */
    private $userWorkScheduleDays = [];

    /**
     * @var StatusChangeDecision
     */
    private $statusChangeDecision;

    /**
     * UserWorkScheduleListener constructor.
     *
     * @param StatusChangeDecision $statusChangeDecision
     */
    public function __construct(StatusChangeDecision $statusChangeDecision)
    {
        $this->statusChangeDecision = $statusChangeDecision;
    }

    /**
     * @param PreUpdateEventArgs $args
     *
     * @throws IncorrectStatusChangeException by StatusChangeDecision::class
     *
     * @todo statusChangeDecision move to validator
     *
     * @return void
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $currentSchedule = $args->getObject();
        if (!$currentSchedule instanceof UserWorkSchedule) {
            return;
        }

        if (
            $args->hasChangedField('status')
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
        }

        if (
            $args->hasChangedField('status')
            && $args->getNewValue('status')->getId() === UserWorkSchedule::STATUS_HR_ACCEPT
        ) {
            $args->getEntityManager()->getRepository(UserWorkSchedule::class)
                ->markPreviousScheduleDaysNotActive($currentSchedule);
        }
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
    }
}
