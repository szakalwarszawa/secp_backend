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
     * @throws NotFoundReferencedUserException
     * @throws Exception
     */
    public function firePreUpdateOnUserTimesheetDayTest(): void
    {
        //workingTime
        $UserTimesheetDay = $this->entityManager->getRepository(UserTimesheetDay::class)->findOneBy(
            array("id" => self::SAMPLE_ID)
        );
        $workingTime = $UserTimesheetDay->getWorkingTime();

        $UserTimesheetDay->setWorkingTime(self::SAMPLE_WORKING_TIME);
        $this->entityManager->flush();

        $workingTimeChanged = $UserTimesheetDay->getWorkingTime();

        $UserTimesheetDayLog = $this->entityManager->getRepository(UserTimesheetDayLog::class)->findOneBy(
            [], ['id' => 'desc']);
        $notice = $UserTimesheetDayLog->getNotice();
        $this->assertStringContainsString('Zmieniono czas pracy z: ' . $workingTime .' na: ' .
            $workingTimeChanged, $notice);
        $this->assertNotEquals($workingTime, $workingTimeChanged);

        //dayStartTime
        $dayStartTime = $UserTimesheetDay->getDayStartTime();
        $UserTimesheetDay->setDayStartTime(self::SAMPLE_START_TIME);
        $this->entityManager->flush();

        $dayStartTimeChanged = $UserTimesheetDay->getDayStartTime();

        $UserTimesheetDayLog = $this->entityManager->getRepository(UserTimesheetDayLog::class)->findOneBy(
            [], ['id' => 'desc']);
        $notice = $UserTimesheetDayLog->getNotice();
        $this->assertStringContainsString('Zmieniono rozpoczecie dnia z: ' . $dayStartTime .' na: ' .
            $dayStartTimeChanged, $notice);
        $this->assertNotEquals($dayStartTime, $dayStartTimeChanged);
    }
}
