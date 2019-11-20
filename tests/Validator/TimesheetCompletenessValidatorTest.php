<?php

declare(strict_types=1);

namespace App\Tests\Validator;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\DataFixtures\UserTimesheetFixtures;
use App\DataFixtures\UserTimesheetStatusFixtures;
use App\Entity\AbsenceType;
use App\Entity\PresenceType;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetDay;
use App\Entity\UserWorkScheduleDay;
use App\Tests\AbstractWebTestCase;
use App\Utils\SpecialId;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

/**
 * Class TimesheetCompletenessValidatorTest
 */
class TimesheetCompletenessValidatorTest extends AbstractWebTestCase
{
    /**
     * @var UserTimesheet
     */
    private $timesheet;

    /**
     * @var UserWorkScheduleDay[]
     */
    private $timesheetWorkScheduleDays;

    /**
     * Test case 0:
     *  - Attempt to change timesheet status from user-edit to user-accept.
     *      Timesheet is not complete, validator should throw exception.
     *
     * @return void
     */
    public function testTimesheetCompletnessValidatorCase0(): void
    {
        $newStatus = $this->getEntityFromReference(UserTimesheetStatusFixtures::REF_STATUS_OWNER_ACCEPT);
        $this->timesheet->setStatus($newStatus);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/(?=.*?\bTimesheet is not complete\b)^.*$/');

        $apiPlatformValidator = self::$container->get(ValidatorInterface::class);
        $apiPlatformValidator->validate($this->timesheet);
    }

    /**
     * Test case 1:
     *  - Insert missing timesheetDays as absence type to complete by user.
     *      Should throw exception.
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testTimesheetCompletnessValidatorCase1(): void
    {
        $specialId = $this::$container->get(SpecialId::class);
        $absenceTypeToCompleteId = $specialId->getIdForSpecialObjectKey('absenceToBeCompletedId');
        $absenceType = $this
            ->entityManager
            ->getRepository(AbsenceType::class)
            ->findOneById($absenceTypeToCompleteId)
            ;
        $presenceAbsence = $this->getEntityFromReference('presence_type_4');
        $this->assertInstanceOf(PresenceType::class, $presenceAbsence);
        $this->assertEquals('N', $presenceAbsence->getShortname());
        $this->assertInstanceOf(AbsenceType::class, $absenceType);
        foreach ($this->timesheetWorkScheduleDays as $workScheduleDay) {
            if (!$workScheduleDay->getUserTimesheetDay()) {
                $userTimesheetDay = new UserTimesheetDay();
                $userTimesheetDay
                    ->setUserWorkScheduleDay($workScheduleDay)
                    ->setAbsenceType($absenceType)
                    ->setPresenceType($presenceAbsence)
                    ->setDayStartTime('8:00')
                    ->setDayEndTime('16:00')
                    ->setWorkingTime(8)
                ;
                $this->entityManager->persist($userTimesheetDay);
            }
        }

        $this->entityManager->flush();

        $newStatus = $this->getEntityFromReference(UserTimesheetStatusFixtures::REF_STATUS_OWNER_ACCEPT);
        $this->timesheet->setStatus($newStatus);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageRegExp('/(?=.*?\bTimesheet is not complete\b)^.*$/');

        $apiPlatformValidator = self::$container->get(ValidatorInterface::class);
        $apiPlatformValidator->validate($this->timesheet);
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->timesheet = $this->getEntityFromReference(
            UserTimesheetFixtures::REF_USER_FILLED_DAYS_TIMESHEET_USER_EDIT
        );

        $this->timesheetWorkScheduleDays = $this
            ->entityManager
            ->getRepository(UserWorkScheduleDay::class)
            ->findWorkDayBetweenDate(
                $this->timesheet->getOwner(),
                $this->timesheet->getPeriodStartDate()->format('Y-m-d'),
                $this->timesheet->getPeriodEndDate()->format('Y-m-d'),
            );
    }
}
