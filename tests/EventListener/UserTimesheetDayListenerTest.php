<?php

namespace App\Tests\EventSubscriber;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetDay;
use App\Entity\UserWorkSchedule;
use App\Entity\WorkScheduleProfile;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class UserTimesheetDayListenerTest extends AbstractWebTestCase
{
    private const TEST_USER_WORK_SCHEDULE_STATUS = UserTimesheet::STATUS_HR_ACCEPT;
    private const TEST_USER_WORK_SCHEDULE_FROM_DATE = '2020-01-01';
    private const TEST_USER_WORK_SCHEDULE_TO_DATE = '2020-01-31';
    private const TEST_USER_REF = UserFixtures::REF_USER_USER;
    private const TEST_WORK_REPORT_DATE = '2020-01-06';

    /**
     * @var int testing record ID
     */
    private $userWorkScheduleId;

    /**
     * @_test
     * @throws \Exception
     */
    public function insertUserTimesheetDayToNonexistentTimesheet(): void
    {
        $userTimesheetDay = new UserTimesheetDay();
        $userTimesheetDay->setUserTimesheet(null)
            ->setPresenceType($this->fixtures->getReference('presence_type_0'))
            ->setAbsenceType(null)
            ->setDayStartTime('09:00')
            ->setDayEndTime(null)
            ->setWorkingTime(8.00)
            ->setDayDate(self::TEST_WORK_REPORT_DATE);

        $this->entityManager->persist($userTimesheetDay);
        $this->entityManager->flush();

        $this->assertIsNumeric($userTimesheetDay->getId());
        $this->assertInstanceOf(UserTimesheet::class, $userTimesheetDay->getUserTimesheet());



        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($this->userWorkScheduleId);
        /* @var $userWorkScheduleUpdated UserWorkSchedule */

        $this->assertNotNull($userWorkScheduleUpdated);
        $this->assertInstanceOf(WorkScheduleProfile::class, $userWorkScheduleUpdated->getWorkScheduleProfile());
        $this->assertEquals(self::TEST_USER_WORK_SCHEDULE_STATUS, $userWorkScheduleUpdated->getStatus());
        $this->assertEquals(
            self::TEST_USER_WORK_SCHEDULE_FROM_DATE,
            $userWorkScheduleUpdated->getFromDate()->format('Y-m-d')
        );
        $this->assertEquals(
            self::TEST_USER_WORK_SCHEDULE_TO_DATE,
            $userWorkScheduleUpdated->getToDate()->format('Y-m-d')
        );

        $days = $userWorkScheduleUpdated->getUserWorkScheduleDays();
        $this->assertCount(31, $days);
    }

    /**
     * @test
     */
    public function updateUserWorkSchedule(): void
    {
        $this->assertIsNumeric($this->userWorkScheduleId);

        $userWorkSchedule = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($this->userWorkScheduleId);
        /* @var $userWorkSchedule UserWorkSchedule */

        $this->assertNotNull($userWorkSchedule);
        $this->assertInstanceOf(WorkScheduleProfile::class, $userWorkSchedule->getWorkScheduleProfile());

        $userWorkSchedule->setStatus(UserWorkSchedule::STATUS_OWNER_ACCEPT);
        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($this->userWorkScheduleId);
        /* @var $userWorkScheduleUpdated UserWorkSchedule */

        $this->assertNotNull($userWorkScheduleUpdated);
        $this->assertInstanceOf(WorkScheduleProfile::class, $userWorkScheduleUpdated->getWorkScheduleProfile());
        $this->assertEquals(UserWorkSchedule::STATUS_OWNER_ACCEPT, $userWorkScheduleUpdated->getStatus());
    }

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $owner = $this->getEntityFromReference(self::TEST_USER_REF);
        $this->assertInstanceOf(User::class, $owner);

        $workScheduleProfile = $this->getEntityFromReference('work_schedule_profile_1');
        $this->assertInstanceOf(WorkScheduleProfile::class, $workScheduleProfile);

        $userWorkSchedule = new UserWorkSchedule();
        $userWorkSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(self::TEST_USER_WORK_SCHEDULE_STATUS)
            ->setFromDate(new \DateTime(self::TEST_USER_WORK_SCHEDULE_FROM_DATE))
            ->setToDate(new \DateTime(self::TEST_USER_WORK_SCHEDULE_TO_DATE));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($userWorkSchedule);
        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $this->userWorkScheduleId = $userWorkSchedule->getId();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function tearDown(): void
    {
        $userWorkSchedule = $this->entityManager->getRepository(UserWorkSchedule::class)->find(
            $this->userWorkScheduleId
        );
        /* @var $userWorkSchedule UserWorkSchedule */

        $timesheets = [];

        foreach ($userWorkSchedule->getUserWorkScheduleDays() as $day) {
            if ($day->getUserTimesheetDay() !== null) {
                $userTimesheet = $day->getUserTimesheetDay()->getUserTimesheet();
                $userTimesheet->removeUserTimesheetDay($day->getUserTimesheetDay());
                $this->entityManager->remove($day->getUserTimesheetDay());
                $timesheets[$userTimesheet->getId()] = $userTimesheet;
            }
            $userWorkSchedule->removeUserWorkScheduleDay($day);
            $this->entityManager->remove($day);
        }

        foreach ($timesheets as $timesheet) {
            $this->entityManager->remove($timesheet);
        }

        $this->entityManager->remove($userWorkSchedule);

        $this->entityManager->flush();

        parent::tearDown();
    }
}
