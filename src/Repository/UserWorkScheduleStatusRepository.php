<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserWorkScheduleStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserWorkScheduleStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserWorkScheduleStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserWorkScheduleStatus[]    findAll()
 * @method UserWorkScheduleStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserWorkScheduleStatusRepository extends ServiceEntityRepository
{
    /**
     * @var RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserWorkScheduleStatus::class);
    }
}
