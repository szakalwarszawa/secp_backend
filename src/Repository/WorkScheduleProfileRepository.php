<?php

namespace App\Repository;

use App\Entity\WorkScheduleProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method WorkScheduleProfile|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkScheduleProfile|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkScheduleProfile[]    findAll()
 * @method WorkScheduleProfile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkScheduleProfileRepository extends ServiceEntityRepository
{
    /**
     * WorkScheduleProfileRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WorkScheduleProfile::class);
    }
}
