<?php

namespace App\Tests\EventSubscriber;

use App\Entity\DayDefinition;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class DayDefinitionLoggerListenerTest extends AbstractWebTestCase
{
    private const DAY_DEFINITION_TEST_ID = '2030-08-11';

    /**
     * @test
     * @throws ORMException
     */
    public function updateDayDefinitionWorkingDay(): void
    {
        /* @var $dayDefinition DayDefinition */
        $dayDefinition = $this->entityManager->getRepository(DayDefinition::class)
            ->find(self::DAY_DEFINITION_TEST_ID);
        $dayDefinition->setWorkingDay(false);
        $this->entityManager->flush();

        $dayDefinitionUpdated = $this->entityManager->getRepository(DayDefinition::class)
            ->find(self::DAY_DEFINITION_TEST_ID);
        /* @var $dayDefinitionUpdated DayDefinition */

        $this->assertNotNull($dayDefinitionUpdated);
        $this->assertEquals(false, $dayDefinitionUpdated->getWorkingDay());
        $this->assertEquals(null, $dayDefinitionUpdated->getNotice());

        $logs = $dayDefinitionUpdated->getDayDefinitionLogs();
        $this->assertCount(1, $logs);

        $this->assertEquals('Dzień został ustawiony jako niepracujący', $logs[0]->getNotice());

        $dayDefinition->setWorkingDay(true);
        $this->entityManager->flush();

        $dayDefinitionUpdated = $this->entityManager->getRepository(DayDefinition::class)
            ->find(self::DAY_DEFINITION_TEST_ID);
        /* @var $dayDefinitionUpdated DayDefinition */

        $this->assertNotNull($dayDefinitionUpdated);
        $this->assertEquals(true, $dayDefinitionUpdated->getWorkingDay());
        $this->assertEquals(null, $dayDefinitionUpdated->getNotice());

        $logs = $dayDefinitionUpdated->getDayDefinitionLogs();
        $this->assertCount(2, $logs);

        $this->assertEquals('Dzień został ustawiony jako pracujący', $logs[1]->getNotice());
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

        $dayDefinition = new DayDefinition();
        $dayDefinition->setId(self::DAY_DEFINITION_TEST_ID);
        $dayDefinition->setWorkingDay(true);
        $dayDefinition->setNotice(null);

        $this->entityManager->persist($dayDefinition);
        $this->entityManager->flush();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function tearDown(): void
    {
        $dayDefinition = $this->entityManager->getRepository(DayDefinition::class)->find(self::DAY_DEFINITION_TEST_ID);
        /* @var $dayDefinition DayDefinition */

        foreach ($dayDefinition->getDayDefinitionLogs() as $log) {
            $dayDefinition->removeDayDefinitionLog($log);
            $this->entityManager->remove($log);
        }
        $this->entityManager->remove($dayDefinition);

        $this->entityManager->flush();

        parent::tearDown();
    }
}
