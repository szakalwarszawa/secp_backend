<?php

namespace App\Repository;

use App\Entity\UserTimesheetDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserTimesheetDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTimesheetDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTimesheetDay[]    findAll()
 * @method UserTimesheetDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTimesheetDayRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserTimesheetDay::class);
    }
}
