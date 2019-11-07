<?php

declare(strict_types=1);

namespace App\Utils;

use DateTime;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

/**
 * Class ReferencePeriod
 * Calculates reference periods date ranges.
 */
class ReferencePeriod
{
    /**
     * @var string
     */
    private const REFERENCE_PERIOD_DURATION = '4 months';

    /**
     * How many periods will be created.
     *
     * @var int
     */
    private const PERIODS_COUNT = 5;

    /**
     * @var array
     */
    public $referencePeriods = [];

    /**
     * ReferencePeriod constructor.
     *
     * @param string $referencePeriodStart
     */
    public function __construct(string $referencePeriodStart)
    {
        try {
            $this->referencePeriods = $this->createReferencePeriods($referencePeriodStart);
        } catch (Exception $e) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unable to create periods, check period start configuration - %s',
                    $referencePeriodStart
                )
            );
        }
    }

    /**
     * Creates ranges of reference periods based on start date.
     *
     * @param string $referencePeriodStart
     *
     * @return array
     * @throws Exception
     */
    private function createReferencePeriods(string $referencePeriodStart): array
    {
        $periods = [];
        $lastDate = null;
        $currentYear = (int) date('Y');
        for ($i = 0; $i < self::PERIODS_COUNT; $i++) {
            $startDate = new DateTimeImmutable(
                sprintf(
                    '%s-%s',
                    $currentYear,
                    $lastDate ?? $referencePeriodStart
                )
            );

            $endDate = $startDate
                ->modify(self::REFERENCE_PERIOD_DURATION)
                ->modify('-1 day');

            $lastDate = $endDate->modify('+1 day')->format('m-d');

            $periods[] = [
                $startDate,
                $endDate,
            ];

            $dateYear = (int) $startDate
                ->modify(self::REFERENCE_PERIOD_DURATION)
                ->format('Y')
            ;

            if ($dateYear !== $currentYear) {
                $currentYear++;
            }
        }

        return $periods;
    }

    /**
     * Get next period range.
     *
     * @return array
     * @throws Exception
     */
    public function getNextPeriod(): array
    {
        $currentPeriodIndex = array_key_first($this->getCurrentPeriod(true));

        return $this->referencePeriods[$currentPeriodIndex + 1];
    }

    /**
     * Returns current period.
     *
     * @param bool $includeIndex
     *
     * @return array
     * @throws Exception
     */
    public function getCurrentPeriod(bool $includeIndex = false): array
    {
        $currentDate = new DateTime();
        foreach ($this->referencePeriods as $index => $period) {
            [$fromDate, $toDate] = $period;

            if (
                $fromDate->getTimestamp() < $currentDate->getTimestamp() &&
                $toDate->getTimestamp() > $currentDate->getTimestamp()
            ) {
                if ($includeIndex) {
                    return [$index => $period];
                }

                return $period;
            }
        }

        throw new InvalidArgumentException(
            'There is something wrong with REFERENCE_PERIOD_DURATION or YEAR_QUARTERS const.'
        );
    }

    /**
     * @return array
     */
    public function getPeriods(): array
    {
        return $this->referencePeriods;
    }
}
