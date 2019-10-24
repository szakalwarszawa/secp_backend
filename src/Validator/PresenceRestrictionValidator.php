<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\PresenceType;
use App\Entity\UserTimesheetDay;
use Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

/**
 * Class PresenceRestrictionValidator
 */
class PresenceRestrictionValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Is incomingValue supported type of data.
     *
     * @param mixed $incomingValue
     *
     * @return bool
     */
    private function supports($incomingValue): bool
    {
        return $incomingValue instanceof UserTimesheetDay;
    }

    /**
     * Validate persisted value.
     * Value must be present in entity class defined in constraint property.
     *
     * @param mixed $entity
     * @param Constraint $constraint
     *
     * @return void
     * @throws Exception
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$this->supports($entity)) {
            return;
        }

        $validations = [
            $this->validateWorkingDay($entity),
            $this->validateCheckDate($entity),
        ];

        if (in_array(false, $validations, true)) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }

    /**
     * Validate date restrictions (EDIT_RESTRICTION_TODAY, EDIT_RESTRICTION_BEFORE_TODAY...)
     *
     * @param UserTimesheetDay $userTimesheetDay
     *
     * @return bool
     *
     * @throws Exception
     */
    private function validateCheckDate(UserTimesheetDay $userTimesheetDay): bool
    {
        $restriction = $this->getRestriction($userTimesheetDay);
        $currentDate = (new DateTime())->setTime(0, 0, 0);
        $userTimesheetDayDate = new DateTime(
            $userTimesheetDay
                ->getUserWorkScheduleDay()
                ->getDayDefinition()
                ->getId()
        );
        $daysDiff = $currentDate->diff($userTimesheetDayDate)->days;

        switch ($restriction) {
            case PresenceType::EDIT_RESTRICTION_ALL:
                return true;
            case PresenceType::EDIT_RESTRICTION_TODAY:
                return $daysDiff === 0;
            case PresenceType::EDIT_RESTRICTION_AFTER_AND_TODAY:
                return $daysDiff >= 0;
            case PresenceType::EDIT_RESTRICTION_AFTER_TODAY:
                return $daysDiff > 0;
            case PresenceType::EDIT_RESTRICTION_BEFORE_AND_TODAY:
                return $daysDiff <= 0;
            case PresenceType::EDIT_RESTRICTION_BEFORE_TODAY:
                return $daysDiff < 0;
            default:
                return false;
        }
    }

    /**
     * Get restriction depends on edit/create entity.
     *
     * @param UserTimesheetDay $userTimesheetDay
     *
     * @return int
     */
    private function getRestriction(UserTimesheetDay $userTimesheetDay): int
    {
        $presenceType = $userTimesheetDay->getPresenceType();
        if ($userTimesheetDay->getId()) {
            return $presenceType->getEditRestriction();
        }

        return $presenceType->getCreateRestriction();
    }

    /**
     * Validate against day type (working or non-working).
     *
     * @param UserTimesheetDay $userTimesheetDay
     *
     * @return bool
     */
    private function validateWorkingDay(UserTimesheetDay $userTimesheetDay): bool
    {
        $presenceType = $userTimesheetDay->getPresenceType();
        $dayDefinition = $userTimesheetDay
            ->getUserWorkScheduleDay()
            ->getDayDefinition()
        ;

        $this->entityManager->initializeObject($dayDefinition);

        if ($presenceType->getWorkingDayRestriction() === PresenceType::RESTRICTION_WORKING_DAY
            && !$dayDefinition->getWorkingDay()
        ) {
            return false;
        }

        if ($presenceType->getWorkingDayRestriction() === PresenceType::RESTRICTION_NON_WORKING_DAY
            && $dayDefinition->getWorkingDay()
        ) {
            return false;
        }

        return true;
    }
}
