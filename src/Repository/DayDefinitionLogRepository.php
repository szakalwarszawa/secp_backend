<?php

namespace App\Repository;

use App\Entity\DayDefinitionLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DayDefinitionLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method DayDefinitionLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method DayDefinitionLog[]    findAll()
 * @method DayDefinitionLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DayDefinitionLogRepository extends ServiceEntityRepository
{
    /**
     * DayDefinitionLogRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DayDefinitionLog::class);
    }
}
