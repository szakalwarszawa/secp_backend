<?php

namespace App\Tests\EventSubscriber;

use App\DataFixtures\UserFixtures;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetDay;
use App\Tests\AbstractWebTestCase;
use RuntimeException;

/**
 * @todo
 */
class UserTimesheetDayListenerTest extends AbstractWebTestCase
{
    private const TEST_WORK_REPORT_DATE = '2020-01-06';

    /**
     * @_test
     */
    public function testInsertUserTimesheetDayToNonexistentTimesheet(): void
    {
        $userTimesheetDay = new UserTimesheetDay();
        $userTimesheetDay->setUserTimesheet(null)
            ->setPresenceType($this->fixtures->getReference('presence_type_0'))
            ->setAbsenceType(null)
            ->setDayStartTime('09:00')
            ->setDayEndTime(null)
            ->setWorkingTime(8.00)
            ->setDayDate(self::TEST_WORK_REPORT_DATE);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing user work schedule day or user schedule is not defined');
        $this->entityManager->persist($userTimesheetDay);
        $this->entityManager->flush();
        $this->assertIsNumeric($userTimesheetDay->getId());
        $this->assertInstanceOf(UserTimesheet::class, $userTimesheetDay->getUserTimesheet());
    }
}
