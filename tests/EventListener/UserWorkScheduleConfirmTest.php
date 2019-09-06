<?php

namespace App\Tests\EventSubscriber;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\WorkScheduleProfile;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class UserWorkScheduleConfirmTest extends AbstractWebTestCase
{
    private const TEST_FROM_DATE = '2020-01-01';
    private const TEST_TO_DATE = '2020-01-31';

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

        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($this->userWorkScheduleId);

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

        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($this->userWorkScheduleId);

        $userWorkScheduleUpdated2 = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($this->userWorkScheduleId2);

        $days = $userWorkScheduleUpdated->getUserWorkScheduleDays();
        foreach($days as $d) {
            var_dump($d->getId());
        }

        $days2 = $userWorkScheduleUpdated2->getUserWorkScheduleDays();

        foreach($days2 as $d) {
            var_dump($d->getId());
        }
        var_dump(count($days));
        var_dump(count($days2));
        var_dump($days2[0]->getUserWorkSchedule()->getId());
        var_dump($days[0]->getUserWorkSchedule()->getId());

        $this->assertCount(31, $days);
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

        $userWorkSchedule = new UserWorkSchedule();
        $userWorkSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(0)
            ->setFromDate(new \DateTime(self::TEST_FROM_DATE))
            ->setToDate(new \DateTime(self::TEST_TO_DATE));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($userWorkSchedule);
        self::$container->get('doctrine')
            ->getManager()
            ->flush();

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

        $this->userWorkScheduleId = $userWorkSchedule->getId();
        $this->userWorkScheduleId2 = $userWorkSchedule2->getId();
    }
}
