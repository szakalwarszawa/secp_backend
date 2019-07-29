<?php

namespace App\Repository;

use App\Entity\UserWorkSchedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserWorkSchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserWorkSchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserWorkSchedule[]    findAll()
 * @method UserWorkSchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserWorkScheduleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserWorkSchedule::class);
    }
}
