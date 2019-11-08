<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use App\Entity\UserWorkScheduleStatus;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class WorkScheduleCreator
 */
class WorkScheduleCreator
{
    /**
     * @var ReferencePeriod
     */
    private $referencePeriod;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserWorkScheduleStatus|null
     */
    private $workScheduleStatus = null;

    /**
     * @param ReferencePeriod $referencePeriod
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ReferencePeriod $referencePeriod, EntityManagerInterface $entityManager)
    {
        $this->referencePeriod = $referencePeriod;
        $this->entityManager = $entityManager;
        $this->workScheduleStatus = $entityManager
            ->getRepository(UserWorkScheduleStatus::class)
            ->findOneById(UserWorkScheduleStatus::HR_ACCEPTED_STATUS)
            ;
    }

    /**
     * @param UserWorkScheduleStatus $workScheduleStatus
     *
     * @return WorkScheduleCreator
     */
    public function setWorkScheduleStatus(UserWorkScheduleStatus $workScheduleStatus): WorkScheduleCreator
    {
        $this->workScheduleStatus = $workScheduleStatus;

        return $this;
    }

    /**
     *
     * If the `dateRange` value is empty|null, an attempt will be made
     * to create a schedule for the next reference period.
     *
     * @param User $user
     * @param array|null $dateRange
     *
     * @return bool
     */
    public function createWorkSchedule(User $user, ?array $dateRange = []): bool
    {
        if (empty($dateRange)) {
            try {
                $dateRange = $this->specifyRange($user);
            } catch (Exception $e) {
                return false;
            }
        }

        if (!$dateRange) {
            return false;
        }

        $this->create($user, $dateRange);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @param User $user
     * @param DateTimeInterface[]|array $dateRange
     *
     * @return void
     */
    private function create(User $user, array $dateRange): void
    {
        [$fromDate, $toDate] = $dateRange;
        $userWorkSchedule = new UserWorkSchedule();
        $userWorkSchedule
            ->setOwner($user)
            ->setFromDate($fromDate)
            ->setToDate($toDate)
            ->setWorkScheduleProfile($user->getDefaultWorkScheduleProfile())
            ->setStatus($this->workScheduleStatus)
            ;

        $this->entityManager->persist($userWorkSchedule);

        return;
    }

    /**
     * Specifies range to create work schedule.
     * If in next period exists any working day, new schedule will be added after this day.
     * If null is returned that means the schedule has already been created by human finger.
     *
     * @param User $user
     *
     * @return array|null
     * @throws Exception
     */
    public function specifyRange(User $user): ?array
    {
        /**
         * @var DateTime $startPeriodDate
         * @var DateTime $endPeriodDate
         */
        [$startPeriodDate, $endPeriodDate] = $this->referencePeriod->getNextPeriod();
        $activeWorkDaysInNextPeriod = $this
            ->entityManager
            ->getRepository(UserWorkScheduleDay::class)
            ->findWorkDayBetweenDate(
                $user,
                $startPeriodDate->format('Y-m-d'),
                $endPeriodDate->format('Y-m-d')
            );

        if ($activeWorkDaysInNextPeriod) {
            $startPeriodDateText = end($activeWorkDaysInNextPeriod)->getDayDefinition()->getId();
            $startPeriodDate = (new DateTimeImmutable($startPeriodDateText))->modify('+1 day');

            if ($startPeriodDate > $endPeriodDate) {
                return null;
            }
        }

        return [
            $startPeriodDate,
            $endPeriodDate,
        ];
    }
}
