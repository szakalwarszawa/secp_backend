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

class UserWorkScheduleChangeStatusTest extends AbstractWebTestCase
{
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
     *
     * 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
     *             9 10 11 12 13 14 15
     * -> 13 14 15 16 17 18 19 (7 deleted)
     */
    public function deleteDaysFromPreviousWorkScheduleTest(): void
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
        $this->assertEquals($this->userWorkScheduleCount1, 17);
        $this->assertEquals($this->userWorkScheduleCount2, 7);
        $this->assertEquals($expectedCount1, 10);
        $this->assertEquals($expectedCount2, 7);
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

        $userWorkSchedule1 = new UserWorkSchedule();
        $userWorkSchedule1->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(0)
            ->setFromDate(new \DateTime('2019-09-03'))
            ->setToDate(new \DateTime('2019-09-19'));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($userWorkSchedule1);

        $userWorkSchedule2 = new UserWorkSchedule();
        $userWorkSchedule2->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(0)
            ->setFromDate(new \DateTime('2020-09-09'))
            ->setToDate(new \DateTime('2020-09-15'));

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
