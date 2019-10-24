<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserWorkScheduleDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserWorkScheduleDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserWorkScheduleDay[]    findAll()
 * @method UserWorkScheduleDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserWorkScheduleDayRepository extends ServiceEntityRepository
{
    /**
     * UserWorkScheduleDayRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserWorkScheduleDay::class);
    }

    /**
     * @param UserWorkSchedule $userWorkSchedule
     * @param string $dayDate
     *
     * @return UserWorkScheduleDay|null
     *
     * @throws NonUniqueResultException
     */
    public function findDayForUserWorkSchedule(
        UserWorkSchedule $userWorkSchedule,
        string $dayDate
    ): ?UserWorkScheduleDay {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.dayDefinition', 'dayDefinition')
            ->andWhere('p.userWorkSchedule = :userWorkSchedule')
            ->setParameter('userWorkSchedule', $userWorkSchedule)
            ->andWhere('dayDefinition.id = :dayDate')
            ->setParameter('dayDate', $dayDate)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param int $userId
     * @param string $dayDate
     *
     * @return UserWorkScheduleDay|null
     *
     * @throws NonUniqueResultException
     */
    public function findWorkDay($userId, $dayDate): ?UserWorkScheduleDay
    {
        $user = $this->getEntityManager()
            ->getRepository(User::class)
            ->find($userId)
        ;

        return $this->createQueryBuilder('p')
            ->innerJoin('p.userWorkSchedule', 'userWorkSchedule')
            ->innerJoin('p.dayDefinition', 'dayDefinition')
            ->innerJoin('userWorkSchedule.status', 'status')
            ->andWhere('userWorkSchedule.owner = :owner')
            ->setParameter('owner', $user)
            ->andWhere('dayDefinition.id = :dayDate')
            ->setParameter('dayDate', $dayDate)
            ->andWhere('status.id = :status')
            ->setParameter('status', UserWorkSchedule::STATUS_HR_ACCEPT)
            ->andWhere('p.active = :active')
            ->setParameter('active', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param User $owner
     * @param string $dayFromDate
     * @param string $dayToDate
     *
     * @return UserWorkScheduleDay[] | null
     */
    public function findWorkDayBetweenDate(User $owner, string $dayFromDate, string $dayToDate): ?array
    {
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.userWorkSchedule', 'userWorkSchedule')
            ->innerJoin('p.dayDefinition', 'dayDefinition')
            ->innerJoin('userWorkSchedule.status', 'status')
            ->andWhere('userWorkSchedule.owner = :owner')
            ->setParameter('owner', $owner)
            ->andWhere('dayDefinition.id >= :dateFrom')
            ->setParameter('dateFrom', $dayFromDate)
            ->andWhere('dayDefinition.id <= :dateTo')
            ->setParameter('dateTo', $dayToDate)
            ->andWhere('status.id = :status')
            ->setParameter('status', UserWorkSchedule::STATUS_HR_ACCEPT)
            ->getQuery();

        $result = $query->getResult();

        return $result ?? null;
    }
}
