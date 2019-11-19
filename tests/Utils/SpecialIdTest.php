<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Entity\AbsenceType;
use App\Entity\PresenceType;
use App\Tests\AbstractWebTestCase;
use App\Utils\SpecialId;
use BadMethodCallException;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\SpecialObjectNotFoundException;
use InvalidArgumentException;
use App\DataFixtures\UserTimesheetStatusFixtures;
use App\Entity\UserTimesheetStatus;

/**
 * Class SpecialIdTest
 */
class SpecialIdTest extends AbstractWebTestCase
{
    /**
     * @return void
     */
    public function testFindOwnerAcceptTimesheetStatus(): void
    {
        $specialIdServiceParams = self::$container->getParameter('app.special_id.parameters');
        $ownerAcceptTimesheetStatusId = $this->makeSpecialIdClass()
            ->getIdForSpecialObjectKey(
                'ownerAcceptTimesheetStatus'
            )
        ;
        $this->assertNotNull($ownerAcceptTimesheetStatusId);
        $this->assertEquals(
            $this->getEntityFromReference(UserTimesheetStatusFixtures::REF_STATUS_OWNER_ACCEPT)->getId(),
            $ownerAcceptTimesheetStatusId
        );

        $ownerAcceptTimesheetStatusDb = $this->entityManager
            ->getRepository(UserTimesheetStatus::class)
            ->findOneById($specialIdServiceParams['ownerAcceptTimesheetStatus'])
        ;
        $this->assertInstanceOf(UserTimesheetStatus::class, $ownerAcceptTimesheetStatusDb);
        $this->assertEquals($ownerAcceptTimesheetStatusDb->getId(), $ownerAcceptTimesheetStatusId);
    }


    /**
     * @return void
     */
    public function testGetToBeCompletedAbsenceObjectId(): void
    {
        $specialIdServiceParams = self::$container->getParameter('app.special_id.parameters');
        $toBeCompletedAbsenceId = $this->makeSpecialIdClass()
            ->getIdForSpecialObjectKey(
                'absenceToBeCompletedId'
            )
        ;
        $this->assertNotNull($toBeCompletedAbsenceId);
        $this->assertIsNumeric($toBeCompletedAbsenceId);

        $toBeCompletedAbsenceIdFromDb = $this->entityManager
            ->getRepository(AbsenceType::class)
            ->findOneBy([
                'shortName' => $specialIdServiceParams['absenceToBeCompletedId'],
            ])
        ;
        $this->assertInstanceOf(AbsenceType::class, $toBeCompletedAbsenceIdFromDb);
        $this->assertEquals($toBeCompletedAbsenceIdFromDb->getId(), $toBeCompletedAbsenceId);
    }

    /**
     * @return void
     */
    public function testGetPresenceAbsenceObjectId(): void
    {
        $specialIdServiceParams = self::$container->getParameter('app.special_id.parameters');
        $presenceAbsenceId = $this->makeSpecialIdClass()
            ->getIdForSpecialObjectKey(
                'presenceAbsenceId'
            )
        ;
        $this->assertNotNull($presenceAbsenceId);
        $this->assertIsNumeric($presenceAbsenceId);

        $presenceAbsenceIdFromDb = $this->entityManager
            ->getRepository(PresenceType::class)
            ->findOneBy([
                'shortName' => $specialIdServiceParams['presenceAbsenceId'],
            ])
        ;
        $this->assertInstanceOf(PresenceType::class, $presenceAbsenceIdFromDb);
        $this->assertEquals($presenceAbsenceIdFromDb->getId(), $presenceAbsenceId);
    }

    /**
     * @return void
     */
    public function testThrowMissedExpectedParameter(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            "Expect finder method for object key: 'nonExistingSpecialObjectKey', " .
            "missing method: 'findNonExistingSpecialObjectKey'"
        );

        $this->makeSpecialIdClass(
            [
                'nonExistingSpecialObjectKey' => 'any value',
            ]
        );
    }

    /**
     * @return void
     */
    public function testThrowNotFindObjectRecordToBeCompletedAbsence(): void
    {
        $this->expectException(SpecialObjectNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Special object with provided key (%s) was not found in database.',
                'non existing record'
            )

        );

        $this->makeSpecialIdClass(
            [
                'absenceToBeCompletedId' => 'non existing record'
            ]
        );
    }

    /**
     * @return void
     */
    public function testThrowNotFindObjectRecordPresenceAbsence(): void
    {
        $this->expectException(SpecialObjectNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Special object with provided key (%s) was not found in database.',
                'non existing record'
            )
        );

        $this->makeSpecialIdClass(
            [
                'presenceAbsenceId' => 'non existing record'
            ]
        );
    }

    /**
     * @return void
     */
    public function testThrowGetWrongObjectKeyRecord(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "You try to get wrong object key: 'nonExistObjectKey'"
        );

        $this->makeSpecialIdClass()->getIdForSpecialObjectKey('nonExistObjectKey');
    }

    /**
     * @param array|null $params
     *
     * @return SpecialId
     */
    private function makeSpecialIdClass(?array $params = null): SpecialId
    {
        if ($params === null) {
            return self::$container->get(SpecialId::class);
        }

        return new SpecialId(
            self::$container->get(EntityManagerInterface::class),
            $params
        );
    }
}
