<?php

namespace App\Tests\EventSubscriber;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\UserWorkScheduleStatusFixtures;
use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleLog;
use App\Entity\UserWorkScheduleStatus;
use App\Entity\WorkScheduleProfile;
use App\Tests\AbstractWebTestCase;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class UserWorkScheduleListenerTest extends AbstractWebTestCase
{
    /**
     * @var string
     */
    private const TEST_FROM_DATE = '2020-01-01';

    /**
     * @var string
     */
    private const TEST_TO_DATE = '2020-01-31';

    /**
     * @var string
     */
    private const INDIVIDUAL_TEST_FROM_DATE = '2021-01-01';

    /**
     * @var string
     */
    private const INDIVIDUAL_TEST_TO_DATE = '2021-01-31';

    /**
     * @var string
     */
    private const INDIVIDUAL_TEST_WORK_TIME_START = '6:44';

    /**
     * @var string
     */
    private const INDIVIDUAL_TEST_WORK_TIME_END = '14:44';

    /**
     * @var int testing record ID
     */
    private $userWorkScheduleId;

    /**
     * @test
     * @throws \Exception
     */
    public function insertUserWorkSchedule(): void
    {
        $this->assertIsNumeric($this->userWorkScheduleId);

        $userWorkScheduleUpdated = $this->entityManager
            ->getRepository(UserWorkSchedule::class)
            ->find($this->userWorkScheduleId);
        /* @var $userWorkScheduleUpdated UserWorkSchedule */

        $this->assertNotNull($userWorkScheduleUpdated);
        $this->assertInstanceOf(WorkScheduleProfile::class, $userWorkScheduleUpdated->getWorkScheduleProfile());
        $workScheduleStatusRef = $this
            ->getEntityFromReference(UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_ACCEPT)
        ;
        $this->assertEquals($workScheduleStatusRef, $userWorkScheduleUpdated->getStatus());
        $this->assertEquals(self::TEST_FROM_DATE, $userWorkScheduleUpdated->getFromDate()->format('Y-m-d'));
        $this->assertEquals(self::TEST_TO_DATE, $userWorkScheduleUpdated->getToDate()->format('Y-m-d'));

        $days = $userWorkScheduleUpdated->getUserWorkScheduleDays();
        $this->assertCount(31, $days);


        /**
         * Test #1 https://redmine.parp.gov.pl/issues/91480
         *
         * Change owner work time
         * Work schedule days params should be inherited from user.
         */
        $this
            ->userMe()
            ->setDayStartTimeFrom(self::INDIVIDUAL_TEST_WORK_TIME_START)
            ->setDayStartTimeTo(self::INDIVIDUAL_TEST_WORK_TIME_START)
            ->setDayEndTimeFrom(self::INDIVIDUAL_TEST_WORK_TIME_END)
            ->setDayEndTimeTo(self::INDIVIDUAL_TEST_WORK_TIME_END)
        ;

        $individualScheduleProfile = $this->getEntityFromReference('work_schedule_profile_1');
        $workScheduleStatusRef = $this
            ->getEntityFromReference(UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT)
        ;

        $this->assertInstanceOf(WorkScheduleProfile::class, $individualScheduleProfile);
        $this->assertEquals('Indywidualny', $individualScheduleProfile->getName());

        $newUserWorkSchedule = new UserWorkSchedule();
        $newUserWorkSchedule
            ->setOwner($this->userMe())
            ->setFromDate(new DateTime(self::INDIVIDUAL_TEST_FROM_DATE))
            ->setToDate(new DateTime(self::INDIVIDUAL_TEST_TO_DATE))
            ->setWorkScheduleProfile($individualScheduleProfile)
            ->setStatus($workScheduleStatusRef)
        ;


        $this->entityManager->persist($newUserWorkSchedule);
        $this->entityManager->flush();

        $days = $newUserWorkSchedule->getUserWorkScheduleDays();
        $this->assertCount(31, $days);
        $firstDay = $days->first();
        $lastDay = $days->last();
        /**
         * Assert that day start time from is not inherited from default profile (individual - 8:30);
         */
        $this->assertNotEquals('8:30', $firstDay->getDayStartTimeFrom());

        $this->assertEquals($firstDay->getDayStartTimeFrom(), self::INDIVIDUAL_TEST_WORK_TIME_START);
        $this->assertEquals($firstDay->getDayStartTimeTo(), self::INDIVIDUAL_TEST_WORK_TIME_START);
        $this->assertEquals($firstDay->getDayEndTimeFrom(), self::INDIVIDUAL_TEST_WORK_TIME_END);
        $this->assertEquals($firstDay->getDayEndTimeTo(), self::INDIVIDUAL_TEST_WORK_TIME_END);

        $this->assertEquals($lastDay->getDayStartTimeFrom(), self::INDIVIDUAL_TEST_WORK_TIME_START);
        $this->assertEquals($lastDay->getDayStartTimeTo(), self::INDIVIDUAL_TEST_WORK_TIME_START);
        $this->assertEquals($lastDay->getDayEndTimeFrom(), self::INDIVIDUAL_TEST_WORK_TIME_END);
        $this->assertEquals($lastDay->getDayEndTimeTo(), self::INDIVIDUAL_TEST_WORK_TIME_END);
        /**
         * End Test #1
         */
    }

    /**
     * @test
     */
    public function updateUserWorkSchedule(): void
    {
        $this->assertIsNumeric($this->userWorkScheduleId);


        $userWorkSchedule = $this->entityManager
            ->getRepository(UserWorkSchedule::class)
            ->find($this->userWorkScheduleId);
        /* @var $userWorkSchedule UserWorkSchedule */

        $this->assertNotNull($userWorkSchedule);
        $this->assertInstanceOf(WorkScheduleProfile::class, $userWorkSchedule->getWorkScheduleProfile());

        $statusBefore = $userWorkSchedule->getStatus();
        $workScheduleStatusRef = $this
            ->getEntityFromReference(UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT)
        ;
        $userWorkSchedule->setStatus($workScheduleStatusRef);
        $this->entityManager
            ->flush();

        $userWorkScheduleUpdated = $this->entityManager
            ->getRepository(UserWorkSchedule::class)
            ->find($this->userWorkScheduleId);
        /* @var $userWorkScheduleUpdated UserWorkSchedule */

        $userWorkScheduleLog = $this->entityManager
            ->getRepository(UserWorkScheduleLog::class)
            ->findOneBy([], ['id' => 'desc']);

        $notice = $userWorkScheduleLog->getNotice();
        $this->assertNotNull($userWorkScheduleUpdated);
        $this->assertStringContainsString(
            sprintf(
                'Zmieniono status z: %s, na: %s',
                $statusBefore->getId(),
                $userWorkScheduleUpdated->getStatus()->getId()
            ),
            $notice
        );
        $this->assertInstanceOf(WorkScheduleProfile::class, $userWorkScheduleUpdated->getWorkScheduleProfile());
        $this->assertEquals($workScheduleStatusRef->getId(), $userWorkScheduleUpdated->getStatus()->getId());
    }

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $owner = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        $this->loginAsUser($owner, ['ROLE_HR']);
        $this->assertInstanceOf(User::class, $owner);

        $workScheduleProfile = $this->getEntityFromReference('work_schedule_profile_1');
        $this->assertInstanceOf(WorkScheduleProfile::class, $workScheduleProfile);

        $userWorkSchedule = new UserWorkSchedule();
        $workScheduleStatusRef = $this
            ->getEntityFromReference(UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_ACCEPT)
        ;
        $this->assertInstanceOf(UserWorkScheduleStatus::class, $workScheduleStatusRef);

        $userWorkSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus($workScheduleStatusRef)
            ->setFromDate(new DateTime(self::TEST_FROM_DATE))
            ->setToDate(new DateTime(self::TEST_TO_DATE));

        $this->entityManager->persist($userWorkSchedule);
        $this->entityManager->flush();

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

        foreach ($userWorkSchedule->getUserWorkScheduleDays() as $day) {
            $userWorkSchedule->removeUserWorkScheduleDay($day);
            $this->entityManager->remove($day);
        }
        $this->entityManager->remove($userWorkSchedule);

        $this->entityManager->flush();

        parent::tearDown();
    }
}
