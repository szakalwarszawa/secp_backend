<?php

namespace App\Repository;

use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserWorkSchedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserWorkSchedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserWorkSchedule[]    findAll()
 * @method UserWorkSchedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserWorkScheduleRepository extends ServiceEntityRepository
{
    /**
     * UserWorkScheduleRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserWorkSchedule::class);
    }

    /**
     * @param $currentSchedule
     *
     * @return void
     */
    public function markPreviousScheduleDaysNotActive($currentSchedule): void
    {
        $this->createQueryBuilder('p')
            ->update(UserWorkScheduleDay::class, 'p')
            ->set('p.active', ':setActive')
            ->setParameter('setActive', false)
            ->andWhere('p.dayDefinition >= :tomorrowDate')
            ->setParameter('tomorrowDate', date('Y-m-d', strtotime('now +1 days')))
            ->andWhere('p.userWorkSchedule != :userWorkSchedule')
            ->setParameter('userWorkSchedule', $currentSchedule)
            ->andWhere('p.dayDefinition BETWEEN :fromDate AND :toDate')
            ->setParameter('fromDate', $currentSchedule->getFromDate()->format('Y-m-d'))
            ->setParameter('toDate', $currentSchedule->getToDate()->format('Y-m-d'))
            ->andWhere('p.active = :previousActive')
            ->setParameter('previousActive', true)
            ->getQuery()
            ->execute();

        $this->createQueryBuilder('p')
            ->update(UserWorkScheduleDay::class, 'p')
            ->set('p.active', ':setActive')
            ->setParameter('setActive', true)
            ->where('p.dayDefinition >= :tomorrowDate')
            ->setParameter('tomorrowDate', date('Y-m-d', strtotime('now +1 days')))
            ->andWhere('p.userWorkSchedule = :userWorkSchedule')
            ->setParameter('userWorkSchedule', $currentSchedule)
            ->getQuery()
            ->execute();
    }
}
