<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Entity\AbsenceType;
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
        $toBeCompletedAbsenceId = $this->makeSpecialIdClass()->getToBeCompletedAbsenceId();
        $this->assertNotNull($toBeCompletedAbsenceId);
        $this->assertIsNumeric($toBeCompletedAbsenceId);

        $toBeCompletedAbsenceIdFromDb = $this->entityManager
            ->getRepository(AbsenceType::class)
            ->findOneBy([
                'shortName' => self::$container->getParameter('app.absence_type.to_be_completed_absence'),
            ])
        ;
        $this->assertInstanceOf(AbsenceType::class, $toBeCompletedAbsenceIdFromDb);
        $this->assertEquals($toBeCompletedAbsenceIdFromDb->getId(), $toBeCompletedAbsenceId);
    }

    /**
     * @return void
     */
    public function testThrowMissedExpectedParameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Expect service param: 'toBeCompletedAbsence'"
        );

        $this->makeSpecialIdClass([]);
    }

    /**
     * @return void
     */
    public function testThrowNotFindObjectRecord(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Don't find special object to be completed absence for given key: 'non existing record'"
        );

        $this->makeSpecialIdClass(
            [
                SpecialId::TO_BE_COMPLETED_ABSENCE_PARAM_KEY => 'non existing record'
            ]
        );
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
