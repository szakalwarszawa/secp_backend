<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetDay;
use App\Entity\UserTimesheetStatus;
use App\Validator\Rules\RuleInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class UserTimesheetEditVoter
 */
class UserTimesheetEditVoter extends Voter
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var string
     */
    public const EDIT_TIMESHEET_DAY = 'EDIT_TIMESHEET_DAY';

    /**
     * @var string
     */
    public const CREATE_TIMESHEET_DAY = 'CREATE_TIMESHEET_DAY';

    /**
     * @var string
     */
    public const REMOVE_TIMESHEET_DAY = 'REMOVE_TIMESHEET_DAY';

    /**
     * UserTimesheetPutVoter constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param Security $security
     */
    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject)
    {
        return in_array(
            $attribute,
            [
                self::EDIT_TIMESHEET_DAY,
                self::CREATE_TIMESHEET_DAY,
                self::REMOVE_TIMESHEET_DAY,
            ]
        ) && $subject instanceof UserTimesheetDay;
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return true;
        /**
         * @var $currentTimesheetStatus UserTimesheetStatus
         */
        $currentTimesheetStatus = $subject->getUserTimesheet()->getStatus();
        $this->entityManager->initializeObject($currentTimesheetStatus);
        return $this->canIEditThis(
            $currentTimesheetStatus->getEditPrivileges(),
            $subject->getUserTimesheet(),
            $token->getUser()
        );
    }

    /**
     * @param array $editPrivileges
     * @param UserTimesheet $userTimesheet
     * @param User $currentUser
     *
     * @return bool
     */
    private function canIEditThis(array $editPrivileges, UserTimesheet $userTimesheet, User $currentUser)
    {
        if (empty($editPrivileges)) {
            return true;
        }

        if (in_array(RuleInterface::OBJECT_OWNER, $editPrivileges)) {
            return $currentUser->getId() === $userTimesheet->getOwner()->getId();
        }

        foreach ($editPrivileges as $roleName) {
            if ($this->security->isGranted($roleName)) {
                return true;
            }
        }

        return false;
    }
}
