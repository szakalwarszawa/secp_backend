<?php

namespace App\Repository;

use App\Entity\UserWorkScheduleLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserWorkScheduleLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserWorkScheduleLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserWorkScheduleLog[]    findAll()
 * @method UserWorkScheduleLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserWorkScheduleLogRepository extends ServiceEntityRepository
{
    /**
     * UserWorkScheduleLogRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserWorkScheduleLog::class);
    }
}
