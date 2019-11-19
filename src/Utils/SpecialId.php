<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\AbsenceType;
use App\Entity\PresenceType;
use BadMethodCallException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\SpecialObjectNotFoundException;
use InvalidArgumentException;
use App\Entity\UserTimesheetStatus;

/**
 * Class SpecialId
 * Simple service to find ID for special object
 */
class SpecialId
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var array
     */
    private $params;

    /**
     * @var array
     */
    private $specialObjects = [];

    /**
     * SpecialId constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param array $params
     *
     * @throws BadMethodCallException
     */
    public function __construct(EntityManagerInterface $entityManager, array $params)
    {
        $this->entityManager = $entityManager;
        $this->params = $params;

        foreach (array_keys($params) as $specialIdKey) {
            $finderFunction = 'find' . ucfirst($specialIdKey);
            if (!method_exists($this, $finderFunction)) {
                throw new BadMethodCallException(
                    sprintf(
                        "Expect finder method for object key: '%s', missing method: '%s'",
                        $specialIdKey,
                        $finderFunction
                    )
                );
            }
            $this->$finderFunction($specialIdKey);
        }
    }

    /**
     * @param string $objectKey
     *
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function getIdForSpecialObjectKey(string $objectKey): ?string
    {
        if (!array_key_exists($objectKey, $this->specialObjects)) {
            throw new InvalidArgumentException(
                sprintf("You try to get wrong object key: '%s'", $objectKey)
            );
        }

        return $this->specialObjects[$objectKey];
    }

    /**
     * @param string $specialIdKey
     *
     * @return void
     * @throws SpecialObjectNotFoundException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function findAbsenceToBeCompletedId(string $specialIdKey): void
    {
        $toBeCompletedAbsence = $this->entityManager
            ->getRepository(AbsenceType::class)
            ->findOneBy([
                'shortName' => $this->params[$specialIdKey],
            ])
        ;

        if (!$toBeCompletedAbsence) {
            throw new SpecialObjectNotFoundException($this->params[$specialIdKey]);
        }

        $this->specialObjects[$specialIdKey] = (string) $toBeCompletedAbsence->getId();
    }

    /**
     * @param string $specialIdKey
     *
     * @return void
     * @throws SpecialObjectNotFoundException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function findPresenceAbsenceId(string $specialIdKey): void
    {
        $presenceAbsence = $this->entityManager
            ->getRepository(PresenceType::class)
            ->findOneBy([
                'shortName' => $this->params[$specialIdKey],
            ])
        ;

        if (!$presenceAbsence) {
            throw new SpecialObjectNotFoundException($this->params[$specialIdKey]);
        }

        $this->specialObjects[$specialIdKey] = (string) $presenceAbsence->getId();
    }

    /**
     * @param string $specialIdKey
     *
     * @return void
     * @throws SpecialObjectNotFoundException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function findOwnerAcceptTimesheetStatus(string $specialIdKey): void
    {
        $ownerAcceptTimesheetStatus = $this
            ->entityManager
            ->getRepository(UserTimesheetStatus::class)
            ->findOneById($this->params[$specialIdKey])
            ;

        if (!$ownerAcceptTimesheetStatus) {
            throw new SpecialObjectNotFoundException($this->params[$specialIdKey]);
        }

        $this->specialObjects[$specialIdKey] = (string) $ownerAcceptTimesheetStatus->getId();
    }
}
