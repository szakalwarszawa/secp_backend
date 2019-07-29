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

    // /**
    //  * @return DayDefinition[] Returns an array of DayDefinition objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DayDefinition
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
