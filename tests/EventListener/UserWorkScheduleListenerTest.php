<?php

namespace App\Tests\EventSubscriber;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\WorkScheduleProfile;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class UserWorkScheduleListenerTest extends AbstractWebTestCase
{
    private const TEST_STATUS = UserWorkSchedule::STATUS_OWNER_ACCEPT;
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
        /* @var $userWorkScheduleUpdated UserWorkSchedule */

        $this->assertNotNull($userWorkScheduleUpdated);
        $this->assertInstanceOf(WorkScheduleProfile::class, $userWorkScheduleUpdated->getWorkScheduleProfile());
        $this->assertEquals(self::TEST_STATUS, $userWorkScheduleUpdated->getStatus());
        $this->assertEquals(self::TEST_FROM_DATE, $userWorkScheduleUpdated->getFromDate()->format('Y-m-d'));
        $this->assertEquals(self::TEST_TO_DATE, $userWorkScheduleUpdated->getToDate()->format('Y-m-d'));

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

        $owner = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        $this->assertInstanceOf(User::class, $owner);

        $workScheduleProfile = $this->getEntityFromReference('work_schedule_profile_1');
        $this->assertInstanceOf(WorkScheduleProfile::class, $workScheduleProfile);

        $userWorkSchedule = new UserWorkSchedule();
        $userWorkSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(self::TEST_STATUS)
            ->setFromDate(new \DateTime(self::TEST_FROM_DATE))
            ->setToDate(new \DateTime(self::TEST_TO_DATE));

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

        foreach ($userWorkSchedule->getUserWorkScheduleDays() as $day) {
            $userWorkSchedule->removeUserWorkScheduleDay($day);
            $this->entityManager->remove($day);
        }
        $this->entityManager->remove($userWorkSchedule);

        $this->entityManager->flush();

        parent::tearDown();
    }
}
