<?php

declare(strict_types=1);

namespace App\Utils;

use DateTime;
use Exception;

/**
 * Class DateTimeHelper
 * Tiny static test helper methods.
 */
class DateTimeHelper
{
    /**
     * Return all month days as array ((string) Y-m-d[])
     *
     * @param int $monthNumber
     *
     * @return array
     * @throws Exception
     */
    public static function getMonthDaysForCurrentYear(int $monthNumber): array
    {
        $dateTime = new DateTime(
            date(sprintf('Y-%02d-01', $monthNumber))
        );
        $monthDays = [$dateTime->format('Y-m-d')];
        for ($i = 1; $i < $dateTime->format('t'); $i++) {
            $monthDays[] = $dateTime->modify('+1 day')->format('Y-m-d');
        }

        return $monthDays;
    }
}
