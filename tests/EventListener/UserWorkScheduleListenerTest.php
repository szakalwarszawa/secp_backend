<?php

namespace App\Tests\EventSubscriber;

use App\DataFixtures\UserFixtures;
use App\Entity\DayDefinition;
use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\WorkScheduleProfile;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class UserWorkScheduleListenerTest extends AbstractWebTestCase
{
    private const DAY_DEFINITION_TEST_ID = '2030-08-11';

    /**
     * @test
     * @throws ORMException
     */
    public function insertUserWorkSchedule(): void
    {
        $owner = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        $this->assertInstanceOf(User::class, $owner);

        $workScheduleProfile = $this->getEntityFromReference('work_schedule_profile_1');
        $this->assertInstanceOf(WorkScheduleProfile::class, $workScheduleProfile);
        $status = 0;
        $fromDate = '2030-01-01';
        $toDate = '2030-01-31';

        $userWorkSchedule = new UserWorkSchedule();
        $userWorkSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus($status)
            ->setFromDate(new \DateTime($fromDate))
            ->setToDate(new \DateTime($toDate));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($userWorkSchedule);
        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $userWorkScheduleId = $userWorkSchedule->getId();
        $this->assertIsNumeric($userWorkScheduleId);

        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($userWorkScheduleId);
        /* @var $userWorkScheduleUpdated UserWorkSchedule */

        $this->assertNotNull($userWorkScheduleUpdated);
        $this->assertInstanceOf(WorkScheduleProfile::class, $userWorkScheduleUpdated->getWorkScheduleProfile());
        $this->assertEquals($workScheduleProfile->getId(), $userWorkScheduleUpdated->getWorkScheduleProfile()->getId());
        $this->assertEquals($status, $userWorkSchedule->getStatus());
        $this->assertEquals($fromDate, $userWorkSchedule->getFromDate()->format('Y-m-d'));
        $this->assertEquals($toDate, $userWorkSchedule->getToDate()->format('Y-m-d'));

        $days = $userWorkScheduleUpdated->getUserWorkScheduleDays();
        $this->assertCount(31, $days);
//
//        $this->assertEquals('Dzień został ustawiony jako niepracujący', $days[0]->getNotice());
//
//        $dayDefinition->setWorkingDay(true);
//        $this->entityManager->flush();
//
//        $dayDefinitionUpdated = $this->entityManager->getRepository(DayDefinition::class)
//            ->find(self::DAY_DEFINITION_TEST_ID);
//        /* @var $dayDefinitionUpdated DayDefinition */
//
//        $this->assertNotNull($dayDefinitionUpdated);
//        $this->assertEquals(true, $dayDefinitionUpdated->getWorkingDay());
//        $this->assertEquals(null, $dayDefinitionUpdated->getNotice());
//
//        $days = $dayDefinitionUpdated->getDayDefinitionLogs();
//        $this->assertCount(2, $days);
//
//        $this->assertEquals('Dzień został ustawiony jako pracujący', $days[1]->getNotice());
    }

    /**
     * @test
     * @throws ORMException
     */
    public function updateDayDefinitionNotice(): void
    {
        $testNotice = 'Nowy testowy opis';
        $testNotice2 = 'Jeszcze nowszy testowy opis';

        /* @var $dayDefinition DayDefinition */
        $dayDefinition = $this->entityManager->getRepository(DayDefinition::class)
            ->find(self::DAY_DEFINITION_TEST_ID);
        $dayDefinition->setNotice($testNotice);
        $this->entityManager->flush();

        $dayDefinitionUpdated = $this->entityManager->getRepository(DayDefinition::class)
            ->find(self::DAY_DEFINITION_TEST_ID);
        /* @var $dayDefinitionUpdated DayDefinition */

        $this->assertNotNull($dayDefinitionUpdated);
        $this->assertEquals(true, $dayDefinitionUpdated->getWorkingDay());
        $this->assertEquals($testNotice, $dayDefinitionUpdated->getNotice());

        $logs = $dayDefinitionUpdated->getDayDefinitionLogs();
        $this->assertCount(1, $logs);

        $this->assertEquals(
            sprintf("Zmieniono opis z:\n%s\nna:\n%s", '', $testNotice),
            $logs[0]->getNotice()
        );

        $dayDefinition->setNotice($testNotice2);
        $this->entityManager->flush();

        $dayDefinitionUpdated = $this->entityManager->getRepository(DayDefinition::class)
            ->find(self::DAY_DEFINITION_TEST_ID);
        /* @var $dayDefinitionUpdated DayDefinition */

        $this->assertNotNull($dayDefinitionUpdated);
        $this->assertEquals(true, $dayDefinitionUpdated->getWorkingDay());
        $this->assertEquals($testNotice2, $dayDefinitionUpdated->getNotice());

        $logs = $dayDefinitionUpdated->getDayDefinitionLogs();
        $this->assertCount(2, $logs);

        $this->assertEquals(
            sprintf("Zmieniono opis z:\n%s\nna:\n%s", $testNotice, $testNotice2),
            $logs[1]->getNotice()
        );

        $dayDefinition->setNotice(null);
        $this->entityManager->flush();

        $dayDefinitionUpdated = $this->entityManager->getRepository(DayDefinition::class)
            ->find(self::DAY_DEFINITION_TEST_ID);
        /* @var $dayDefinitionUpdated DayDefinition */

        $this->assertNotNull($dayDefinitionUpdated);
        $this->assertEquals(true, $dayDefinitionUpdated->getWorkingDay());
        $this->assertEquals(null, $dayDefinitionUpdated->getNotice());

        $logs = $dayDefinitionUpdated->getDayDefinitionLogs();
        $this->assertCount(3, $logs);

        $this->assertEquals(
            sprintf("Zmieniono opis z:\n%s\nna:\n%s", $testNotice2, ''),
            $logs[2]->getNotice()
        );
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function setUp(): void
    {
        parent::setUp();

    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function tearDown(): void
    {

        parent::tearDown();
    }
}
