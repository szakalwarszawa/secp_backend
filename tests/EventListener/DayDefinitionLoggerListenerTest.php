<?php

namespace App\Tests\EventSubscriber;

use App\Entity\DayDefinition;
use App\Entity\DayDefinitionLog;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\Events;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class DayDefinitionLoggerListenerTest extends AbstractWebTestCase
{
    /**
     * @test
     */
    public function updateDayDefinition(): void
    {
        $dayDefinition = $this->getEntityFromReference('day_definition_test');
        /* @var $dayDefinition DayDefinition */
//        dd($this->entityManager->getEventManager()->getListeners(Events::preUpdate));
        $dayDefinitionId = $dayDefinition->getId();
        $dayDefinition->setWorkingDay(false);
        $dayDefinition->setNotice('adad');
        $this->entityManager->flush();

        $dayDefinitionUpdated = $this->entityManager->getRepository(DayDefinition::class)->find($dayDefinitionId);
        /* @var $dayDefinitionUpdated DayDefinition */

        $this->assertNotNull($dayDefinitionUpdated);
        $this->assertEquals(false, $dayDefinitionUpdated->getWorkingDay());
//        $this->assertEquals(null, $dayDefinitionUpdated->getNotice());
        $this->assertNotCount(0, $dayDefinitionUpdated->getDayDefinitionLogs());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $dayDefinition = new DayDefinition();
        $dayDefinition->setId('2030-08-11');
        $dayDefinition->setWorkingDay(true);
        $dayDefinition->setNotice(null);

        $this->entityManager->persist($dayDefinition);
        $this->entityManager->flush();

        $this->fixtures->addReference('day_definition_test', $dayDefinition);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function tearDown(): void
    {
//        $dayDefinition = $this->getEntityFromReference('day_definition_test');
//        /* @var $dayDefinition DayDefinition */
//
//        foreach ($dayDefinition->getDayDefinitionLogs() as $log) {
//            $dayDefinition->removeDayDefinitionLog($log);
//            $this->entityManager->remove($log);
//        }
//        $this->entityManager->remove($dayDefinition);
//
//        $this->entityManager->flush();

        parent::tearDown();
    }
}
