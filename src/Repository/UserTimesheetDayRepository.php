<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserTimesheetDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserTimesheetDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTimesheetDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTimesheetDay[]    findAll()
 * @method UserTimesheetDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTimesheetDayRepository extends ServiceEntityRepository
{
    /**
     * UserTimesheetDayRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserTimesheetDay::class);
    }

    /**
     * @param User $owner
     * @param string $dayFromDate
     * @param string $dayToDate
     * @return UserTimesheetDay[]|null
     */
    public function findWorkDayBetweenDate(User $owner, string $dayFromDate, string $dayToDate): ?array
    {
        $query = $this->createQueryBuilder('p')
            ->innerJoin('p.userTimesheet', 'userTimesheet')
            ->innerJoin('p.userWorkScheduleDay', 'userWorkScheduleDay')
            ->innerJoin('userWorkScheduleDay.dayDefinition', 'dayDefinition')
            ->andWhere('userTimesheet.owner = :owner')
            ->setParameter('owner', $owner)
            ->andWhere('dayDefinition.id >= :dateFrom')
            ->setParameter('dateFrom', $dayFromDate)
            ->andWhere('dayDefinition.id <= :dateTo')
            ->setParameter('dateTo', $dayToDate)
            ->getQuery();

        $result = $query->getResult();

        return $result ?? null;
    }

    /**
     * @param User $user
     * @param string $dayFromDate
     * @param string $dayToDate
     * @param bool $asObjects
     *
     * @return UserTimesheetDay[]|null
     */
    public function findTimesheetDaysBetweenDate(
        User $user,
        string $dayFromDate,
        string $dayToDate,
        bool $asObjects = false
    ): ?array {
        $queryBuilder = $this->createQueryBuilder('p');
        if (!$asObjects) {
            $queryBuilder->select('
                dayDefinition.id as dayId,
                p.dayStartTime,
                p.dayEndTime,
                p.workingTime,
                absenceType.shortName as absenceShortName,
                case when absenceType != 0 then owner.dailyWorkingTime else 0 end as absenceTime
            ');
        }
        $queryBuilder
            ->innerJoin('p.userTimesheet', 'userTimesheet')
            ->leftJoin('p.absenceType', 'absenceType')
            ->innerJoin('userTimesheet.owner', 'owner')
            ->innerJoin('p.userWorkScheduleDay', 'userWorkScheduleDay')
            ->innerJoin('userWorkScheduleDay.dayDefinition', 'dayDefinition')
            ->andWhere('userTimesheet.owner = :owner')
            ->setParameter('owner', $user)
            ->andWhere('dayDefinition.id >= :dateFrom')
            ->setParameter('dateFrom', $dayFromDate)
            ->andWhere('dayDefinition.id <= :dateTo')
            ->setParameter('dateTo', $dayToDate)
            ->orderBy('dayDefinition.id')
        ;

        $result = $queryBuilder->getQuery()->getResult();

        return $result ?? null;
    }
}
