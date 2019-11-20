<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\UserTimesheetFixtures;
use App\Entity\PresenceType;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetDay;
use App\Security\Voter\UserTimesheetEditVoter;
use App\Tests\AbstractWebTestCase;
use App\Validator\Rules\RuleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * Class UserTimesheetEditVoterTest
 */
class UserTimesheetEditVoterTest extends AbstractWebTestCase
{
    /**
     * @var UserTimesheetDay|null
     */
    private $userTimesheetDay = null;

    /**
     * @var UserTimesheet|null
     */
    private $userTimesheet = null;

    /**
     * Test case 0:
     *  - Attempt to edit UserTimesheetDay.
     *      UserTimesheet has edit privileges set as ROLE_USER so anyone at this status can make changes.
     *      Voter should return true.
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testUserTimesheetEditVoterCase0(): void
    {
        $currentTimesheetStatus = $this->userTimesheet->getStatus();
        $this->entityManager->initializeObject($currentTimesheetStatus);
        /**
         * Make sure that current status is editable for everyone.
         */
        $currentTimesheetStatus->setEditPrivileges(['ROLE_USER']);
        $this->entityManager->flush();
        $voteResult = $this->security->isGranted(UserTimesheetEditVoter::EDIT_TIMESHEET_DAY, $this->userTimesheetDay);

        $this->assertTrue($voteResult);
    }

    /**
     * Test case 1:
     *  - Attempt to edit UserTimesheetDay.
     *      UserTimesheet has edit privileges set as ROLE_ADMIN so only ADMIN can manage this day.
     *      Voter should return false because current user has no required role.
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testUserTimesheetEditVoterCase1(): void
    {
        $currentTimesheetStatus = $this->userTimesheet->getStatus();
        $this->entityManager->initializeObject($currentTimesheetStatus);
        /**
         * Make sure that current status is editable only for ROLE_ADMIN.
         * Current user should not be able to edit this.
         */
        $currentTimesheetStatus->setEditPrivileges(['ROLE_ADMIN']);
        $this->entityManager->flush();
        $voteResult = $this->security->isGranted(UserTimesheetEditVoter::EDIT_TIMESHEET_DAY, $this->userTimesheetDay);

        $this->assertFalse($voteResult);
    }

    /**
     * Test case 2:
     *  - Attempt to edit UserTimesheetDay.
     *      UserTimesheet has edit privileges set as ROLE_ADMIN so only ADMIN can create new day.
     *      Voter should return false because current user has no required role to create day.
     *      After change status edit privileges to ROLE_USER, voter should return true.
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testUserTimesheetEditVoterCase2(): void
    {
        $currentTimesheetStatus = $this->userTimesheet->getStatus();
        $this->entityManager->initializeObject($currentTimesheetStatus);
        /**
         * Make sure that current status is editable only for ROLE_ADMIN.
         * Current user should not be able to edit this.
         */
        $currentTimesheetStatus->setEditPrivileges(['ROLE_ADMIN']);
        $this->entityManager->flush();

        /**
         * @var $presenceTypeRef PresenceType
         */
        $presenceTypeRef = $this->fixtures->getReference('presence_type_6');
        $timesheetDay = new UserTimesheetDay();
        $timesheetDay
            ->setPresenceType($presenceTypeRef)
            ->setDayStartTime('8:00')
            ->setDayEndTime('16:00')
            ->setWorkingTime(8)
            ->setUserTimesheet($this->userTimesheet)
            ;

        $voteResult = $this->security->isGranted(UserTimesheetEditVoter::CREATE_TIMESHEET_DAY, $timesheetDay);
        $this->assertFalse($voteResult);

        $currentTimesheetStatus->setEditPrivileges(['ROLE_USER']);
        $this->entityManager->flush();

        $voteResult = $this->security->isGranted(UserTimesheetEditVoter::CREATE_TIMESHEET_DAY, $timesheetDay);

        $this->assertTrue($voteResult);
    }

    /**
     * Test case 3:
     *  - Attempt to edit UserTimesheetDay.
     *      UserTimesheet has edit privileges set as `owner` so only owner can manage days at this stage.
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testUserTimesheetEditVoterCase3(): void
    {
        $currentTimesheetStatus = $this->userTimesheet->getStatus();
        $this->entityManager->initializeObject($currentTimesheetStatus);
        /**
         * Make sure that current status is editable only for owner.
         */
        $currentTimesheetStatus->setEditPrivileges([RuleInterface::OBJECT_OWNER]);
        $this->entityManager->flush();

        $voteResult = $this->security->isGranted(UserTimesheetEditVoter::EDIT_TIMESHEET_DAY, $this->userTimesheetDay);
        $this->assertTrue($voteResult);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $userTimesheetREF = $this->fixtures->getReference(UserTimesheetFixtures::REF_USER_TIMESHEET_USER_EDIT);
        /**
         * @var $userTimesheetDays ArrayCollection
         */
        $userTimesheetDays = $userTimesheetREF->getUserTimesheetDays();
        $this->userTimesheetDay = $userTimesheetDays->get(array_rand($userTimesheetDays->toArray()));
        $this->userTimesheet = $userTimesheetREF;

        $this->assertNotNull($this->userTimesheetDay);
        $this->assertNotNull($this->userTimesheet);

        $user = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        $this->loginAsUser($user, ['ROLE_USER']);

        $this->assertNotNull($this->userMe());
    }
}
