<?php

namespace App\Repository;

use App\Entity\DayDefinition;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DayDefinition|null find($id, $lockMode = null, $lockVersion = null)
 * @method DayDefinition|null findOneBy(array $criteria, array $orderBy = null)
 * @method DayDefinition[]    findAll()
 * @method DayDefinition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DayDefinitionRepository extends ServiceEntityRepository
{
    /**
     * DayDefinitionRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DayDefinition::class);
    }

    /**
     * @param string $dateFrom
     * @param string $dateTo
     * @return DayDefinition[]
     */
    public function findAllBetweenDate($dateFrom, $dateTo): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.id >= :dateFrom')
            ->andWhere('p.id <= :dateTo')
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->orderBy('p.id', 'ASC')
            ->getQuery();

        return $qb->execute();
    }

    /**
     * @return DayDefinition|null
     *
     * @throws Exception
     */
    public function findTodayDayDefinition(): ?DayDefinition
    {
        try {
            return $this
                ->createQueryBuilder('d')
                ->where('d.id = :today')
                ->setParameter('today', (new DateTime())->format('Y-m-d'))
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
