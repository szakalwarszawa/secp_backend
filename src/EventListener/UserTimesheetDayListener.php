<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetDay;
use App\Entity\UserTimesheetStatus;
use App\Entity\UserWorkScheduleDay;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use RuntimeException;

/**
 * Class UserTimesheetDayListener
 */
class UserTimesheetDayListener
{
    /**
     * @param LifecycleEventArgs $args
     *
     * @return void
     *
     * @throws ORMException
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entityManager = $args->getEntityManager();
        /* @var EntityManager $entityManager */

        $userTimesheetDay = $args->getObject();
        /* @var $userTimesheetDay UserTimesheetDay */
        if (!$userTimesheetDay instanceof UserTimesheetDay) {
            return;
        }
        if ($userTimesheetDay->getUserTimesheet() !== null) {
            return;
        }

        $userWorkScheduleDay = $userTimesheetDay->getUserWorkScheduleDay();
        if ($userWorkScheduleDay === null || !$userWorkScheduleDay instanceof UserWorkScheduleDay) {
            throw new RuntimeException('Missing user work schedule day or user schedule is not defined');
        }

        if ($userWorkScheduleDay->getDayDefinition() === null) {
            throw new RuntimeException('Missing user work schedule day');
        }

        $userTimesheet = $this->getUserTimesheet($entityManager, $userWorkScheduleDay);

        $userTimesheetDay->setUserTimesheet($userTimesheet);
    }

    /**
     * @param EntityManager $entityManager
     * @param UserWorkScheduleDay $userWorkScheduleDay
     *
     * @return UserTimesheet
     *
     * @throws ORMException
     */
    private function getUserTimesheet(
        EntityManager $entityManager,
        UserWorkScheduleDay $userWorkScheduleDay
    ): UserTimesheet {
        $period = date(
            'Y-m',
            strtotime($userWorkScheduleDay->getDayDefinition()->getId())
        );
        $owner = $userWorkScheduleDay->getUserWorkSchedule()->getOwner();

        $userTimesheet = $entityManager
            ->getRepository(UserTimesheet::class)
            ->findByUserPeriod(
                $owner,
                $period
            );

        if ($userTimesheet === null) {
            $userTimesheet = new UserTimesheet();
            $userTimesheet
                ->setStatus($entityManager->getRepository(UserTimesheetStatus::class)->getStatusOwnerEdit())
                ->setPeriod($period)
                ->setOwner($owner);

            $entityManager->persist($userTimesheet);
            $entityManager->getUnitOfWork()
                ->computeChangeSet(
                    $entityManager->getClassMetadata(UserTimesheet::class),
                    $userTimesheet
                );
        }

        return $userTimesheet;
    }
}
