<?php

declare(strict_types=1);

namespace App\Utils;

use DateTime;

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
     */
    public static function getMonthDays(int $monthNumber): array
    {
        $dateTime = new DateTime(
            date(
                sprintf(
                    'Y-%s-01',
                    $monthNumber
                )
            )
        );
        $monthDays = [];
        $monthDays[] = $dateTime->format('Y-m-d');
        $monthDaysCount = $dateTime->format('t');
        for ($i = 1; $i < $monthDaysCount; $i++) {
            $monthDays[] = $dateTime->modify('+1 day')->format('Y-m-d');
        }

        return $monthDays;
    }
}
