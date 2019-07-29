<?php

namespace App\Repository;

use App\Entity\UserWorkScheduleDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserWorkScheduleDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserWorkScheduleDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserWorkScheduleDay[]    findAll()
 * @method UserWorkScheduleDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserWorkScheduleDayRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserWorkScheduleDay::class);
    }
}
