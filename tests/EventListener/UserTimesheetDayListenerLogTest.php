<?php

namespace App\Tests\EventListener;

use App\Entity\UserTimesheetDay;
use App\Entity\UserTimesheetDayLog;
use App\Tests\AbstractWebTestCase;
use Exception;

class UserTimesheetDayListenerLogTest extends AbstractWebTestCase
{
    /**
     * @var int
     */
    private const SAMPLE_ID = 2;

    /**
     * @var float
     */
    private const SAMPLE_WORKING_TIME = 9.06;

    /**
     * @var string
     */
    private const SAMPLE_START_TIME = '10:30';

    /**
     * @test
     * @throws Exception
     */
    public function checkWorkingTimeUpdateOnUserTimesheetDayTest(): void
    {
        $userTimesheetDay = $this->getTestedUserTimesheetDay();

        $workingTimeOriginal = $userTimesheetDay->getWorkingTime();

        $userTimesheetDay->setWorkingTime(self::SAMPLE_WORKING_TIME);
        $this->entityManager->flush();

        $workingTimeChanged = $userTimesheetDay->getWorkingTime();

        $userTimesheetDayLog = $this->getLastUserTimesheetDayLog();

        $this->assertEquals(
            'Zmieniono czas pracy z: ' . $workingTimeOriginal . ', na: ' . $workingTimeChanged,
            $userTimesheetDayLog->getNotice()
        );
        $this->assertEquals(self::SAMPLE_WORKING_TIME, $workingTimeChanged);
    }

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

    /**
     * @return UserTimesheetDay|null
     */
    private function getTestedUserTimesheetDay(): ?UserTimesheetDay
    {
        return $this->entityManager
            ->getRepository(UserTimesheetDay::class)
            ->findOneBy(
                ['id' => self::SAMPLE_ID]
            );
    }

    /**
     * @return UserTimesheetDayLog|null
     */
    private function getLastUserTimesheetDayLog(): ?UserTimesheetDayLog
    {
        return $this->entityManager
            ->getRepository(UserTimesheetDayLog::class)
            ->findOneBy(
                [],
                ['id' => 'desc']
            );
    }
}
