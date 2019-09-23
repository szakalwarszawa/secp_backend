<?php

namespace App\Repository;

use App\Entity\UserTimesheetLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserTimesheetLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTimesheetLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTimesheetLog[]    findAll()
 * @method UserTimesheetLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTimesheetLogRepository extends ServiceEntityRepository
{
    /**
     * UserTimesheetLogRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserTimesheetLog::class);
    }
}
