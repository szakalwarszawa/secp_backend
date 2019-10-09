<?php

namespace App\Repository;

use App\Entity\UserTimesheetStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserTimesheetStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTimesheetStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTimesheetStatus[]    findAll()
 * @method UserTimesheetStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTimesheetStatusRepository extends ServiceEntityRepository
{
    /**
     * @var string
     */
    public const TIMESHEET_STATUS_OWNER_EDIT = 'TIMESHEET-STATUS-OWNER-EDIT';

    /**
     * UserTimesheetStatusRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserTimesheetStatus::class);
    }

    /**
     * @return UserTimesheetStatus|null
     */
    public function getStatusOwnerEdit(): ?UserTimesheetStatus
    {
        return $this->find(self::TIMESHEET_STATUS_OWNER_EDIT);
    }
}
