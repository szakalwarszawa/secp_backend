<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Entity\AbsenceType;
use App\Entity\PresenceType;
use App\Tests\AbstractWebTestCase;
use App\Utils\SpecialId;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

/**
 * Class SpecialIdTest
 */
class SpecialIdTest extends AbstractWebTestCase
{
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
        $this->expectException(InvalidArgumentException::class);
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Don't find special object to be completed absence for given key: 'non existing record'"
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Don't find special object to be absence type of presence given key: 'non existing record'"
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
    private function makeSpecialIdClass($params = null): SpecialId
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
