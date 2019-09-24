<?php

namespace App\Repository;

use App\Entity\UserTimesheetDayLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserTimesheetDayLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTimesheetDayLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTimesheetDayLog[]    findAll()
 * @method UserTimesheetDayLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTimesheetDayLogRepository extends ServiceEntityRepository
{
    /**
     * UserTimesheetDayLogRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserTimesheetDayLog::class);
    }
}
