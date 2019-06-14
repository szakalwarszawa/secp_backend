<?php

namespace App\Repository;

use App\Entity\DayDefinition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DayDefinition|null find($id, $lockMode = null, $lockVersion = null)
 * @method DayDefinition|null findOneBy(array $criteria, array $orderBy = null)
 * @method DayDefinition[]    findAll()
 * @method DayDefinition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DayDefinitionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DayDefinition::class);
    }
}
