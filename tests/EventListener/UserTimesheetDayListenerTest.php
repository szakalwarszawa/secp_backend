<?php

namespace App\Tests\EventSubscriber;

use App\DataFixtures\UserTimesheetFixtures;
use App\DataFixtures\UserWorkScheduleFixtures;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetDay;
use App\Entity\UserTimesheetDayLog;
use App\Entity\UserWorkSchedule;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use RuntimeException;

/**
 * Class UserTimesheetDayListenerTest
 */
class UserTimesheetDayListenerTest extends AbstractWebTestCase
{
    /**
     * @var string
     */
    private const TEST_WORK_REPORT_DATE = '2020-01-06';

    /**
     * User timesheet with empty fields
     *
     * @var UserTimesheetDay
     */
    private $emptyUserTimesheetDay;

    /**
     * User timesheet with filled fields
     *
     * @var UserTimesheetDay
     */
    private $filledUserTimesheetDay;

    /**
     * @throws ORMException
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

    /**
     * @return array
     */
    public function userTimesheetDayChangeFromEmptyProvider(): array
    {
        return [
            [['setNotice', null, 'test notice', 'Zmieniono opis z: brak, na: test notice']],
            [['setWorkingTime', null, 7.50, 'Zmieniono czas pracy z: 8, na: 7.5']],
            [['setDayStartTime', null, '07:25', 'Zmieniono rozpoczęcie dnia z: brak, na: 07:25']],
            [['setDayEndTime', null, '15:55', 'Zmieniono zakończenie dnia z: brak, na: 15:55']],
            [['setPresenceType', 'presence_type_2', null, 'Zmieniono typ obecności z: obecność, na: szkolenie']],
            [['setAbsenceType', 'absence_type_2', null, 'Zmieniono typ nieobecności z: brak, na: urlop na żądanie']],
        ];
    }

    /**
     * @dataProvider userTimesheetDayChangeFromEmptyProvider
     * @throws ORMException
     */
    public function testUserTimesheetDayChangeFromEmpty($testCase): void
    {
        [$fieldName, $newValueReference, $newValue, $expectedMessage] = $testCase;
        $newValue = $newValueReference !== null
            ? $this->getEntityFromReference($newValueReference)
            : $newValue;

        $this->entityManager->refresh($this->emptyUserTimesheetDay);

        $this->emptyUserTimesheetDay->$fieldName($newValue);
        $this->entityManager->persist($this->emptyUserTimesheetDay);
        $this->entityManager->flush();

        $userTimesheetDayLogs = $this->entityManager
            ->getRepository(UserTimesheetDayLog::class)
            ->findBy([
                'userTimesheetDay' => $this->emptyUserTimesheetDay,
            ]);
        /* @var UserTimesheetDayLog[] $userTimesheetDayLogs */

        $this->assertCount(1, $userTimesheetDayLogs);
        $this->assertEquals($expectedMessage, $userTimesheetDayLogs[0]->getNotice());
    }

    /**
     * @return array
     */
    public function userTimesheetDayChangeFromFilledProvider(): array
    {
        return [
            [['setNotice', null, 'test notice', 'Zmieniono opis z: first notice, na: test notice']],
            [['setWorkingTime', null, 7.50, 'Zmieniono czas pracy z: 8, na: 7.5']],
            [['setDayStartTime', null, '07:25', 'Zmieniono rozpoczęcie dnia z: 09:00, na: 07:25']],
            [['setDayEndTime', null, '15:55', 'Zmieniono zakończenie dnia z: 17:00, na: 15:55']],
            [['setPresenceType', 'presence_type_2', null, 'Zmieniono typ obecności z: obecność, na: szkolenie']],
            [[
                'setAbsenceType',
                'absence_type_2',
                null,
                'Zmieniono typ nieobecności z: urlop wypoczynkowy, na: urlop na żądanie'
            ]],
        ];
    }

    /**
     * @dataProvider userTimesheetDayChangeFromFilledProvider
     * @throws ORMException
     */
    public function testUserTimesheetDayChangeFromFilled($testCase): void
    {
        [$fieldName, $newValueReference, $newValue, $expectedMessage] = $testCase;
        $newValue = $newValueReference !== null
            ? $this->getEntityFromReference($newValueReference)
            : $newValue;

        $this->entityManager->refresh($this->filledUserTimesheetDay);

        $this->filledUserTimesheetDay->$fieldName($newValue);
        $this->entityManager->persist($this->filledUserTimesheetDay);
        $this->entityManager->flush();

        $userTimesheetDayLogs = $this->entityManager
            ->getRepository(UserTimesheetDayLog::class)
            ->findBy([
                'userTimesheetDay' => $this->filledUserTimesheetDay,
            ]);
        /* @var UserTimesheetDayLog[] $userTimesheetDayLogs */

        $this->assertCount(1, $userTimesheetDayLogs);
        $this->assertEquals($expectedMessage, $userTimesheetDayLogs[0]->getNotice());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $userTimesheet = $this->getEntityFromReference(UserTimesheetFixtures::REF_USER_TIMESHEET_USER_EDIT);
        /* @var $userTimesheet UserTimesheet */
        $userWorkSchedule = $this->getEntityFromReference(UserWorkScheduleFixtures::REF_USER_WORK_SCHEDULE_USER_HR);
        /* @var $userWorkSchedule UserWorkSchedule */

        $userTimesheetDayEmpty = new UserTimesheetDay();
        $userTimesheetDayEmpty->setUserTimesheet($userTimesheet)
            ->setUserWorkScheduleDay(
                $userWorkSchedule->getUserWorkScheduleDays()[count($userWorkSchedule->getUserWorkScheduleDays()) -1]
            )
            ->setPresenceType($this->getEntityFromReference('presence_type_0'))
            ->setAbsenceType(null)
            ->setWorkingTime(8.00)
            ->setDayDate(self::TEST_WORK_REPORT_DATE);

        $this->entityManager->persist($userTimesheetDayEmpty);
        $this->entityManager->flush();
        $this->emptyUserTimesheetDay = $userTimesheetDayEmpty;

        $userTimesheetDayFilled = new UserTimesheetDay();
        $userTimesheetDayFilled->setUserTimesheet($userTimesheet)
            ->setUserWorkScheduleDay(
                $userWorkSchedule->getUserWorkScheduleDays()[count($userWorkSchedule->getUserWorkScheduleDays()) -2]
            )
            ->setPresenceType($this->getEntityFromReference('presence_type_0'))
            ->setAbsenceType($this->getEntityFromReference('absence_type_0'))
            ->setDayStartTime('09:00')
            ->setDayEndTime('17:00')
            ->setWorkingTime(8.00)
            ->setNotice('first notice')
            ->setDayDate(self::TEST_WORK_REPORT_DATE);

        $this->entityManager->persist($userTimesheetDayFilled);
        $this->entityManager->flush();
        $this->filledUserTimesheetDay = $userTimesheetDayFilled;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function tearDown(): void
    {
        foreach ($this->emptyUserTimesheetDay->getUserTimesheetDayLogs() as $log) {
            $this->emptyUserTimesheetDay->removeUserTimesheetDayLog($log);
            $this->entityManager->remove($log);
        }
        $this->entityManager->remove($this->emptyUserTimesheetDay);

        foreach ($this->filledUserTimesheetDay->getUserTimesheetDayLogs() as $log) {
            $this->filledUserTimesheetDay->removeUserTimesheetDayLog($log);
            $this->entityManager->remove($log);
        }
        $this->entityManager->remove($this->filledUserTimesheetDay);

        $this->entityManager->flush();

        parent::tearDown();
    }
}
