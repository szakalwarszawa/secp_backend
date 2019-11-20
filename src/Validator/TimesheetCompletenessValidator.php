<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetStatus;
use App\Entity\UserWorkScheduleDay;
use App\Utils\SpecialId;
use App\Utils\UserUtilsInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class TimesheetCompletenessValidator
 */
class TimesheetCompletenessValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SpecialId
     */
    private $specialId;

    /**
     * @var UserUtilsInterface
     */
    private $userUtil;

    /**
     * TimesheetCompletnessValidator constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SpecialId $specialId
     * @param UserUtilsInterface $userUtil
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SpecialId $specialId,
        UserUtilsInterface $userUtil
    ) {
        $this->entityManager = $entityManager;
        $this->specialId = $specialId;
        $this->userUtil = $userUtil;
    }

    /**
     * Is supported type data.
     *
     * @param mixed $value
     *
     * @return bool
     */
    private function supports($value): bool
    {
        return $value instanceof UserTimesheetStatus;
    }

    /**
     * @param mixed $value
     * @param Constraint $constraint
     *
     * @return void
     * @throws Exception
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$this->supports($value)) {
            return;
        }

        /**
         * @var $userTimesheet UserTimesheet
         */
        $userTimesheet = $this->context->getObject();
        if (
            $value->getId() !== $this->specialId->getIdForSpecialObjectKey('ownerAcceptTimesheetStatus')
            || $userTimesheet->getOwner() !== $this->userUtil->getCurrentUser()
        ) {
            return;
        }

        $timesheetWorkScheduleDays = $this
            ->entityManager
            ->getRepository(UserWorkScheduleDay::class)
            ->findWorkDayBetweenDate(
                $userTimesheet->getOwner(),
                $userTimesheet->getPeriodStartDate()->format('Y-m-d'),
                $userTimesheet->getPeriodEndDate()->format('Y-m-d')
            );

        $missingDays = [];
        foreach ($timesheetWorkScheduleDays as $workScheduleDay) {
            if ($this->isMissingDay($workScheduleDay)) {
                $missingDays[] = $workScheduleDay->getDayDefinition()->getDay();
            }
        }

        if (!empty($missingDays)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->setParameter('{{value}}', implode(', ', $missingDays))
                ->addViolation()
            ;
        }
    }

    /**
     * Check if there are any uncompleted days in work schedule.
     * Criteria:
     *  - All UserWorkScheduleDay marked as `workingDay` should have UserTimesheetDay set.
     *  - UserWorkScheduleDay with absence type (reason) `absenceToBeCompletedId` is not acceptable
     *
     * @param UserWorkScheduleDay $userWorkScheduleDay
     *
     * @return bool
     */
    private function isMissingDay(UserWorkScheduleDay $userWorkScheduleDay): bool
    {
        $userTimesheetDay = $userWorkScheduleDay->getUserTimesheetDay();
        $dayDefinition = $userWorkScheduleDay->getDayDefinition();
        $absenceType = $userTimesheetDay !== null ? $userTimesheetDay->getAbsenceType() : null;
        $absenceToCompleteId = (int) $this->specialId->getIdForSpecialObjectKey('absenceToBeCompletedId');

        $uncompletedDay = $dayDefinition->isWorkingDay() && !$userTimesheetDay;
        $absenceUncompleted = $absenceType !== null ? $absenceType->getId() === $absenceToCompleteId : false;

        return $uncompletedDay || $absenceUncompleted;
    }
}
