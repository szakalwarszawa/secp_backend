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

class UserWorkScheduleConfirmTest extends AbstractWebTestCase
{
    /**
     *
     */
    private const TEST_FROM_DATE = '2020-01-01';
    /**
     *
     */
    private const TEST_TO_DATE = '2020-01-31';

    /**
     * @var int|null
     */
    private $userWorkScheduleId1;
    /**
     * @var int|null
     */
    private $userWorkScheduleId2;
    /**
     * @var int
     */
    private $userWorkScheduleCount1;
    /**
     * @var int
     */
    private $userWorkScheduleCount2;

    /**
     * @test
     * @throws \Exception
     */
    public function insertUserWorkSchedule(): void
    {
        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($this->userWorkScheduleId1);

        $userWorkScheduleUpdated2 = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($this->userWorkScheduleId2);

        $userWorkScheduleUpdated->setStatus(3);
        $userWorkScheduleUpdated2->setStatus(3);

        self::$container->get('doctrine')
            ->getManager()
            ->persist($userWorkScheduleUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->persist($userWorkScheduleUpdated2);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $userWorkScheduleUpdated1 = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $this->userWorkScheduleId1));

        $expectedCount1 = count($userWorkScheduleUpdated1);

        $userWorkScheduleUpdated2 = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $this->userWorkScheduleId2));

        $expectedCount2 = count($userWorkScheduleUpdated2);

        $this->assertEquals($expectedCount1, 0);
        $this->assertEquals($expectedCount2, 31);
        $this->assertEquals($this->userWorkScheduleCount1, 31);
        $this->assertEquals($this->userWorkScheduleCount2, 31);
    }

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $owner = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        $this->assertInstanceOf(User::class, $owner);

        $workScheduleProfile = $this->getEntityFromReference('work_schedule_profile_1');
        $this->assertInstanceOf(WorkScheduleProfile::class, $workScheduleProfile);

        $userWorkSchedule1 = new UserWorkSchedule();
        $userWorkSchedule1->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(0)
            ->setFromDate(new \DateTime(self::TEST_FROM_DATE))
            ->setToDate(new \DateTime(self::TEST_TO_DATE));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($userWorkSchedule1);

        $userWorkSchedule2 = new UserWorkSchedule();
        $userWorkSchedule2->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(0)
            ->setFromDate(new \DateTime(self::TEST_FROM_DATE))
            ->setToDate(new \DateTime(self::TEST_TO_DATE));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($userWorkSchedule2);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $this->userWorkScheduleId1 = $userWorkSchedule1->getId();
        $this->userWorkScheduleId2 = $userWorkSchedule2->getId();

        $userWorkScheduleUpdated2 = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $this->userWorkScheduleId1));

        $this->userWorkScheduleCount1 = count($userWorkScheduleUpdated2);

        $userWorkScheduleUpdated2 = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $this->userWorkScheduleId2));

        $this->userWorkScheduleCount2 = count($userWorkScheduleUpdated2);
    }
}
