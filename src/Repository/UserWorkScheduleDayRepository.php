<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserWorkScheduleDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserWorkScheduleDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserWorkScheduleDay[]    findAll()
 * @method UserWorkScheduleDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserWorkScheduleDayRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserWorkScheduleDay::class);
    }

    /**
     * @param UserWorkSchedule $userWorkSchedule
     * @param string $dayDate
     * @return UserWorkScheduleDay|null
     */
    public function findDayForUserWorkSchedule(
        UserWorkSchedule $userWorkSchedule,
        string $dayDate
    ): ?UserWorkScheduleDay {
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.dayDefinition', 'dayDefinition')
            ->andWhere('p.userWorkSchedule = :userWorkSchedule')
            ->andWhere('dayDefinition.id = :dayDate')
            ->setParameter('userWorkSchedule', $userWorkSchedule)
            ->setParameter('dayDate', $dayDate)
            ->setMaxResults(1)
            ->getQuery();

        $result = $query->getResult();

        return $result[0] ?? null;
    }

    /**
     * @param User $owner
     * @param string $dayDate
     * @return UserWorkScheduleDay|null
     */
    public function findWorkDay($owner, $dayDate): ?UserWorkScheduleDay
    {
        // @Todo dodać uwzględnianie tylko zatwierdzonych harmonogramów
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.userWorkSchedule', 'userWorkSchedule')
            ->innerJoin('p.dayDefinition', 'dayDefinition')
            ->andWhere('userWorkSchedule.owner = :owner')
            ->andWhere('dayDefinition.id = :dayDate')
            ->setParameter('owner', $owner)
            ->setParameter('dayDate', $dayDate)
            ->setMaxResults(1)
            ->getQuery();

        $result = $query->getResult();

        return $result[0] ?? null;
    }

    /**
     * @param User $owner
     * @param string $dayFromDate
     * @param string $dayToDate
     * @return UserWorkScheduleDay[]|null
     */
    public function findWorkDayBetweenDate($owner, $dayFromDate, $dayToDate): ?array
    {
        // @Todo dodać uwzględnianie tylko zatwierdzonych harmonogramów
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.userWorkSchedule', 'userWorkSchedule')
            ->innerJoin('p.dayDefinition', 'dayDefinition')
            ->andWhere('userWorkSchedule.owner = :owner')
            ->setParameter('owner', $owner)
            ->andWhere('dayDefinition.id >= :dateFrom')
            ->setParameter('dateFrom', $dayFromDate)
            ->andWhere('dayDefinition.id <= :dateTo')
            ->setParameter('dateTo', $dayToDate)
            ->getQuery();

        $result = $query->getResult();

        return $result ?? null;
    }
}
