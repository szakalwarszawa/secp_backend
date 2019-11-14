<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\AbsenceType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

/**
 * Class SpecialId
 * Simple service to find ID for special object
 *
 * @package App\Utils
 */
class SpecialId
{
    /**
     * @var string
     */
    public const TO_BE_COMPLETED_ABSENCE_PARAM_KEY = 'toBeCompletedAbsence';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var array
     */
    private $params;

    /**
     * @var null
     */
    private $toBeCompletedAbsenceId = null;

    /**
     * SpecialId constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param array $params
     */
    public function __construct(EntityManagerInterface $entityManager, array $params)
    {
        $this->entityManager = $entityManager;
        $this->params = $params;

        $this->findToBeCompletedAbsenceId();
    }

    /**
     * @return integer|null
     */
    public function getToBeCompletedAbsenceId(): ?int
    {
        return $this->toBeCompletedAbsenceId;
    }

    /**
     * @return void
     */
    private function findToBeCompletedAbsenceId(): void
    {
        if (!isset($this->params[self::TO_BE_COMPLETED_ABSENCE_PARAM_KEY])) {
            throw new InvalidArgumentException(
                sprintf("Expect service param: '%s'", self::TO_BE_COMPLETED_ABSENCE_PARAM_KEY)
            );
        }

        $toBeCompletedAbsence = $this->entityManager
            ->getRepository(AbsenceType::class)
            ->findOneBy([
                'shortName' => $this->params[self::TO_BE_COMPLETED_ABSENCE_PARAM_KEY],
            ])
        ;

        if ($toBeCompletedAbsence === null) {
            throw new InvalidArgumentException(
                sprintf(
                    "Don't find special object to be completed absence for given key: '%s'",
                    $this->params[self::TO_BE_COMPLETED_ABSENCE_PARAM_KEY]
                )
            );
        }

        $this->toBeCompletedAbsenceId = $toBeCompletedAbsence->getId();
    }
}
