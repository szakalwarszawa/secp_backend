<?php

namespace App\Tests\EventSubscriber;

use App\Entity\UserTimesheetDayLog;
use App\Entity\UserTimesheetDay;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use Exception;

class UserTimesheetDayListenerLogTest extends AbstractWebTestCase
{
    private const SAMPLE_ID = 2;
    private const SAMPLE_WORKING_TIME = 9.06;
    private const SAMPLE_START_TIME = 10.30;

    /**
     * @test
     * @throws Exception
     */
    public function checkStartTimeUpdateOnUserTimesheetDayTest(): void
    {
        $userTimesheetDay = $this->getTestedUserTimesheetDay();
        $dayStartTimeOriginal = $userTimesheetDay->getDayStartTime();

        $userTimesheetDay->setDayStartTime(self::SAMPLE_START_TIME);
        $this->entityManager->flush();

        $dayStartTimeChanged = $userTimesheetDay->getDayStartTime();

        $userTimesheetDayLog = $this->getLastUserTimesheetDayLog();

        $this->assertEquals(
            'Zmieniono rozpoczęcie dnia z: ' . ($dayStartTimeOriginal ?? 'brak') . ', na: ' . $dayStartTimeChanged,
            $userTimesheetDayLog->getNotice()
        );
        $this->assertEquals(self::SAMPLE_START_TIME, $dayStartTimeChanged);
    }
}
