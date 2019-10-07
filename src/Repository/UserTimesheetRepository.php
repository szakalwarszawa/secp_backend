<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserTimesheet|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTimesheet|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTimesheet[]    findAll()
 * @method UserTimesheet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTimesheetRepository extends ServiceEntityRepository
{
    /**
     * UserTimesheetRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserTimesheet::class);
    }

    /**
     * @param User $owner
     * @param string $period
     *
     * @return UserTimesheet|null
     */
    public function findByUserPeriod(User $owner, string $period): ?UserTimesheet
    {
        $result = $this->createQueryBuilder('p')
            ->where('p.owner = :owner')
            ->setParameter('owner', $owner)
            ->andWhere('p.period = :period')
            ->setParameter('period', $period)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return $result[0] ?? null;
    }
}
