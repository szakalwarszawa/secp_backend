<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserWorkScheduleDay;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class UserActiveWorkScheduleDayAction
 */
class UserActiveWorkScheduleDayAction
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $userId
     * @param string $dayDate
     *
     * @return UserWorkScheduleDay|null
     */
    public function __invoke(int $userId, string $dayDate): ?UserWorkScheduleDay
    {
        return $this->entityManager
            ->getRepository(UserWorkScheduleDay::class)
            ->findWorkDay($userId, $dayDate)
        ;
    }
}
