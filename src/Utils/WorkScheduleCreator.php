<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\User;
use App\Entity\UserWorkScheduleDay;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * Class WorkScheduleCreator
 * @package App\Utils
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

    public function __construct(ReferencePeriod $referencePeriod, EntityManagerInterface $entityManager)
    {
        $this->referencePeriod = $referencePeriod;
        $this->entityManager = $entityManager;
    }

    /**
     *
     * If the `dateRange` value is empty|null, an attempt will be made
     * to create a schedule for the next reference period.
     *
     * @param array|null $dateRange
     *
     * @return bool
     */
    public function createWorkSchedule(?array $dateRange = []): bool
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => 2037]);
        if (empty($dateRange)) {
            try {
                $dateRange = $this->specifyRange($user);
            } catch (Exception $e) {
                return false;
            }
        }

        if ($dateRange === false) {
            return false;
        }


        return true;

        //todo
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
    private function specifyRange(User $user): ?array
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
            $startPeriodDate = end($activeWorkDaysInNextPeriod)->getDayDefinition()->getId();
        }

        $dayAfter = (new DateTimeImmutable($startPeriodDate))->modify('+1 day');
        if ($dayAfter > $endPeriodDate) {
            return null;
        }
        return [
            $dayAfter,
            $endPeriodDate,
        ];
    }
}
