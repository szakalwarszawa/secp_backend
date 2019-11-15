<?php

declare(strict_types=1);

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
     * @var int
     */
    public const RETURN_AS_REPORT_ARRAY = 1;

    /**
     * Flat values intended for report.
     *
     * @var int
     */
    public const RETURN_AS_DEFAULT_OBJECTS = 2;

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
     * Finds user`s timesheetdays between two dates.
     * Depending on the parameter `returnType` it could be flatten array for report
     * or default UserTimesheetDay objects.
     *
     * @param User $user
     * @param string $dayFromDate
     * @param string $dayToDate
     * @param int $returnType
     *
     * @return UserTimesheetDay[]|null
     */
    public function findTimesheetDaysBetweenDate(
        User $user,
        string $dayFromDate,
        string $dayToDate,
        int $returnType = self::RETURN_AS_DEFAULT_OBJECTS
    ): ?array {
        $queryBuilder = $this->createQueryBuilder('p');
        if ($returnType === self::RETURN_AS_REPORT_ARRAY) {
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
