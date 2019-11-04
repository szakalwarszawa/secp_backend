<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\DataFixtures\UserTimesheetFixtures;
use App\DataFixtures\UserWorkScheduleFixtures;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetDay;
use App\Entity\UserWorkScheduleDay;
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
     * @return void
     *
     * @throws ORMException
     */
    public function testInsertUserTimesheetDayToNonexistentTimesheet(): void
    {
        $userWorkSchedule = $this->getEntityFromReference(
            UserWorkScheduleFixtures::REF_FIXED_USER_WORK_SCHEDULE_ADMIN_HR
        );
        /* @var UserWorkSchedule $userWorkSchedule */

        $userWorkScheduleDay = $userWorkSchedule->getUserWorkScheduleDays()[0];
        $this->assertInstanceOf(UserWorkScheduleDay::class, $userWorkScheduleDay);
        $this->assertNull($userWorkScheduleDay->getUserTimesheetDay());
        $this->assertCountUserTimesheet(0, $userWorkScheduleDay);

        $userTimesheetDay = $this->addUserTimesheetDay($userWorkScheduleDay);
        $this->assertIsNumeric($userTimesheetDay->getId());
        $this->assertInstanceOf(UserTimesheet::class, $userTimesheetDay->getUserTimesheet());
        $this->assertCountUserTimesheet(1, $userWorkScheduleDay);

        $userWorkScheduleDay = $userWorkSchedule->getUserWorkScheduleDays()[1];
        $userTimesheetDay = $this->addUserTimesheetDay($userWorkScheduleDay);
        $this->assertIsNumeric($userTimesheetDay->getId());
        $this->assertInstanceOf(UserTimesheet::class, $userTimesheetDay->getUserTimesheet());
        $this->assertCountUserTimesheet(1, $userWorkScheduleDay);
    }

    /**
     * @return void
     *
     * @throws ORMException
     */
    public function testInsertUserTimesheetDayToNonexistentSchedule(): void
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
     * @return void
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $userTimesheet = $this->getEntityFromReference(UserTimesheetFixtures::REF_USER_TIMESHEET_USER_EDIT);
        /* @var $userTimesheet UserTimesheet */
        $userWorkSchedule = $this->getEntityFromReference(
            UserWorkScheduleFixtures::REF_FIXED_USER_WORK_SCHEDULE_USER_HR
        );
        /* @var $userWorkSchedule UserWorkSchedule */

        $userTimesheetDayEmpty = new UserTimesheetDay();
        $userTimesheetDayEmpty->setUserTimesheet($userTimesheet)
            ->setUserWorkScheduleDay(
                $userWorkSchedule->getUserWorkScheduleDays()[count($userWorkSchedule->getUserWorkScheduleDays()) - 1]
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
                $userWorkSchedule->getUserWorkScheduleDays()[count($userWorkSchedule->getUserWorkScheduleDays()) - 2]
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
     * @return void
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function tearDown(): void
    {
        foreach ($this->emptyUserTimesheetDay->getLogs() as $log) {
            $this->emptyUserTimesheetDay->removeLog($log);
            $this->entityManager->remove($log);
        }
        $this->entityManager->remove($this->emptyUserTimesheetDay);

        foreach ($this->filledUserTimesheetDay->getLogs() as $log) {
            $this->filledUserTimesheetDay->removeLog($log);
            $this->entityManager->remove($log);
        }
        $this->entityManager->remove($this->filledUserTimesheetDay);

        $this->entityManager->flush();

        parent::tearDown();
    }

    /**
     * @param int $expectedCount
     * @param UserWorkScheduleDay $userWorkScheduleDay
     *
     * @return void
     */
    private function assertCountUserTimesheet(int $expectedCount, UserWorkScheduleDay $userWorkScheduleDay): void
    {
        $period = date('Y-m', strtotime($userWorkScheduleDay->getDayDefinition()->getId()));

        $userTimesheet = $this->entityManager
            ->getRepository(UserTimesheet::class)
            ->findBy(
                [
                    'owner' => $userWorkScheduleDay->getUserWorkSchedule()->getOwner(),
                    'period' => $period
                ]
            );

        $this->assertCount($expectedCount, $userTimesheet, $period);
    }

    /**
     * @param UserWorkScheduleDay $userWorkScheduleDay
     *
     * @return UserTimesheetDay
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function addUserTimesheetDay(UserWorkScheduleDay $userWorkScheduleDay): UserTimesheetDay
    {
        $userTimesheetDay = new UserTimesheetDay();
        $userTimesheetDay
            ->setPresenceType($this->getEntityFromReference('presence_type_0'))
            ->setAbsenceType(null)
            ->setDayStartTime('09:00')
            ->setDayEndTime('17:00')
            ->setWorkingTime(8.00)
            ->setUserWorkScheduleDay($userWorkScheduleDay)
            ->setDayDate($userWorkScheduleDay->getDayDefinition()->getId());

        $this->entityManager->persist($userTimesheetDay);
        $this->entityManager->flush();

        return $userTimesheetDay;
    }
}
