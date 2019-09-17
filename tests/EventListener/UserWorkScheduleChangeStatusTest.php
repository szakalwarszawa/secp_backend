<?php

namespace App\Tests\EventSubscriber;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use App\Entity\WorkScheduleProfile;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Class UserWorkScheduleChangeStatusTest
 * @package App\Tests\EventSubscriber
 */
class UserWorkScheduleChangeStatusTest extends AbstractWebTestCase
{
    /**
     * @var int|null
     */
    private $previousScheduleId;

    /**
     * @var int|null
     */
    private $currentScheduleId;

    /**
     * @var int
     */
    private $previousWorkScheduleCount;

    /**
     * @var int
     */
    private $currentWorkScheduleCount;

    /**
     * @var int
     */
    const NO_CHANGES = 0;

    /**
     * @var int
     */
    const CHANGED_DAYS = 2;

    /**
     * @var int
     */
    const CURRENT_SCHEDULE_DAYS = 6;

    /**
     * @var int
     */
    const PREVIOUS_SCHEDULE_DAYS = 6;

    /**
     * @var Date
     */
    const START_FROM = '2019-09-14';

    /**
     * @var Date
     */
    const START_TO = '2019-09-19';

    /**
     * @var Date
     */
    const END_FROM = '2020-09-16';

    /**
     * @var Date
     */
    const END_TO = '2020-09-21';

    /**
     * @var int
     */
    const STATUS_HR_ACCEPT = 3;

    /**
     * @test
     * @throws \Exception
     *
     * 14 15 16 17 18 19
     *       16 17 18 19 20 21
     * -> 18 19 (2 changed)
     *
     * possibile also scenarios:
     *
     *       15 16 17 18 19 20 21
     * 13 14 15 16 17 18
     * -> 18 19 20 21 (4 changed)
     *
     *      17 18 19 20 21 22
     *      17 18 19 20 21 22
     * -> 18 19 20 21 22 (5 changed)
     */
    public function markAsDeletedFromPreviousWorkScheduleTest(): void
    {
        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($this->previousScheduleId);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($this->currentScheduleId);

        $userWorkScheduleUpdated->setStatus(UserWorkScheduleChangeStatusTest::STATUS_HR_ACCEPT);
        $currentUpdated->setStatus(UserWorkScheduleChangeStatusTest::STATUS_HR_ACCEPT);

        self::$container->get('doctrine')
            ->getManager()
            ->persist($userWorkScheduleUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->persist($currentUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $previousUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $this->previousScheduleId, "visibility" => false));

        $previousScheduleAffectedDays = count($previousUpdated);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $this->currentScheduleId, "visibility" => false));

        $currentScheduleAffectedDays = count($currentUpdated);

        $this->assertEquals($this->previousWorkScheduleCount,UserWorkScheduleChangeStatusTest::CURRENT_SCHEDULE_DAYS);
        $this->assertEquals($this->currentWorkScheduleCount,UserWorkScheduleChangeStatusTest::PREVIOUS_SCHEDULE_DAYS);
        $this->assertEquals($previousScheduleAffectedDays,UserWorkScheduleChangeStatusTest::CHANGED_DAYS);
        $this->assertEquals($currentScheduleAffectedDays,UserWorkScheduleChangeStatusTest::NO_CHANGES);
    }

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $owner = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        $this->assertInstanceOf(User::class, $owner);

        $workScheduleProfile = $this->getEntityFromReference('work_schedule_profile_2');
        $this->assertInstanceOf(WorkScheduleProfile::class, $workScheduleProfile);

        $previousSchedule = new UserWorkSchedule();
        $previousSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(0)
            ->setFromDate(new \DateTime(UserWorkScheduleChangeStatusTest::START_FROM))
            ->setToDate(new \DateTime(UserWorkScheduleChangeStatusTest::START_TO));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($previousSchedule);

        $currentSchedule = new UserWorkSchedule();
        $currentSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(0)
            ->setFromDate(new \DateTime('2020-09-16'))
            ->setToDate(new \DateTime('2020-09-21'));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($currentSchedule);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $this->previousScheduleId = $previousSchedule->getId();
        $this->currentScheduleId = $currentSchedule->getId();

        $userWorkScheduleUpdatedPrevious = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $this->previousScheduleId));

        $this->previousWorkScheduleCount = count($userWorkScheduleUpdatedPrevious);

        $userWorkScheduleUpdatedCurrent = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $this->currentScheduleId));

        $this->currentWorkScheduleCount = count($userWorkScheduleUpdatedCurrent);
    }
}
