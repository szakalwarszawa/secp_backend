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
     * @var array
     */
    public $referencePeriods = [];

    /**
     * ReferencePeriod constructor.
     *
     * @param string $referencePeriodsRanges
     */
    public function __construct(string $referencePeriodsRanges)
    {
        try {
            $this->referencePeriods = $this->createReferencePeriods($referencePeriodsRanges);
        } catch (Exception $e) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unable to create periods, check period start configuration - %s',
                    $referencePeriodsRanges
                )
            );
        }
    }

    /**
     * Creates ranges of reference periods based on start date.
     *
     * @param string $referencePeriodsRanges
     *
     * @return array
     * @throws Exception
     */
    private function createReferencePeriods(string $referencePeriodsRanges): array
    {
        $referencePeriodsRanges = explode(',', $referencePeriodsRanges);
        $periodDates = [];
        $firstPeriod = null;
        foreach ($referencePeriodsRanges as $period) {
            [$periodStart, $periodEnd] = explode('--', $period);
            $period = [
                new DateTimeImmutable(date('Y-' . $periodStart)),
                new DateTimeImmutable(date('Y-' . $periodEnd))
            ];

            if (!$firstPeriod) {
                $firstPeriod = $period;
            }

            $periodDates[] = $period;
        }

        $periodDates[] = [
            $firstPeriod[0]->modify('+1 year'),
            $firstPeriod[1]->modify('+1 year'),
        ];

        return $periodDates;
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
     * @throws InvalidArgumentException when consts are invalid
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
