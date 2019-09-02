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
        $userTimesheetDay = $this->entityManager
            ->getRepository(UserTimesheetDay::class)
            ->findOneBy(array('id' => self::SAMPLE_ID)
        );
        $workingTime = $userTimesheetDay->getWorkingTime();

        $userTimesheetDay->setWorkingTime(self::SAMPLE_WORKING_TIME);
        $this->entityManager->flush();

        $workingTimeChanged = $userTimesheetDay->getWorkingTime();

        $userTimesheetDayLog = $this->entityManager
            ->getRepository(UserTimesheetDayLog::class)
            ->findOneBy([], ['id' => 'desc']);
        $notice = $userTimesheetDayLog->getNotice();
        $this->assertStringContainsString('Zmieniono czas pracy z: ' . $workingTime .' na: ' .
            $workingTimeChanged, $notice);
        $this->assertNotEquals($workingTime, $workingTimeChanged);

        //dayStartTime
        $dayStartTime = $userTimesheetDay->getDayStartTime();
        $userTimesheetDay->setDayStartTime(self::SAMPLE_START_TIME);
        $this->entityManager->flush();

        $dayStartTimeChanged = $userTimesheetDay->getDayStartTime();

        $userTimesheetDayLog = $this->entityManager
            ->getRepository(UserTimesheetDayLog::class)
            ->findOneBy([], ['id' => 'desc']);
        $notice = $userTimesheetDayLog->getNotice();
        $this->assertStringContainsString('Zmieniono rozpoczęcie dnia z: ' . $dayStartTime .' na: ' .
            $dayStartTimeChanged, $notice);
        $this->assertNotEquals($dayStartTime, $dayStartTimeChanged);
    }
}
