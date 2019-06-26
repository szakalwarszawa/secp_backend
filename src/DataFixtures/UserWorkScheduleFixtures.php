<?php

namespace App\DataFixtures;

use App\Entity\DayDefinition;
use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use App\Entity\WorkScheduleProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class UserWorkScheduleFixtures extends Fixture implements DependentFixtureInterface
{
    public const REF_USER_WORK_SCHEDULE_ADMIN_HR = 'user_work_schedule_admin_hr';
    public const REF_USER_WORK_SCHEDULE_ADMIN_EDIT = 'user_work_schedule_admin_edit';
    public const REF_USER_WORK_SCHEDULE_MANAGER_HR = 'user_work_schedule_manager_hr';
    public const REF_USER_WORK_SCHEDULE_USER_HR = 'user_work_schedule_user_hr';
    public const REF_USER_WORK_SCHEDULE_USER_OWNER_ACCEPT = 'user_work_schedule_user_owner_accept';

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return array(
            UserFixtures::class,
            DayDefinitionFixtures::class,
            WorkScheduleProfileFixtures::class,
        );
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $this->makeUserWorkScheduleSets(
            $manager,
            self::REF_USER_WORK_SCHEDULE_ADMIN_HR,
            $this->getReference(UserFixtures::REF_USER_ADMIN),
            $this->getReference('work_schedule_profile_0'),
            UserWorkSchedule::STATUS_HR_ACCEPT,
            '2019-05-01',
            '2019-08-31'
        );

        $this->makeUserWorkScheduleSets(
            $manager,
            self::REF_USER_WORK_SCHEDULE_ADMIN_EDIT,
            $this->getReference(UserFixtures::REF_USER_ADMIN),
            $this->getReference('work_schedule_profile_0'),
            UserWorkSchedule::STATUS_OWNER_EDIT,
            '2019-07-01',
            '2019-08-31'
        );

        $this->makeUserWorkScheduleSets(
            $manager,
            self::REF_USER_WORK_SCHEDULE_MANAGER_HR,
            $this->getReference(UserFixtures::REF_USER_MANAGER),
            $this->getReference('work_schedule_profile_0'),
            UserWorkSchedule::STATUS_HR_ACCEPT,
            '2019-05-01',
            '2019-08-31'
        );

        $this->makeUserWorkScheduleSets(
            $manager,
            self::REF_USER_WORK_SCHEDULE_USER_HR,
            $this->getReference(UserFixtures::REF_USER_USER),
            $this->getReference('work_schedule_profile_0'),
            UserWorkSchedule::STATUS_HR_ACCEPT,
            '2019-05-01',
            '2019-08-31'
        );

        $this->makeUserWorkScheduleSets(
            $manager,
            self::REF_USER_WORK_SCHEDULE_USER_OWNER_ACCEPT,
            $this->getReference(UserFixtures::REF_USER_USER),
            $this->getReference('work_schedule_profile_0'),
            UserWorkSchedule::STATUS_OWNER_ACCEPT,
            '2019-07-01',
            '2019-08-31'
        );

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string $referenceName
     * @param User $owner
     * @param WorkScheduleProfile $workScheduleProfile
     * @param int $status
     * @param string $fromDate
     * @param string $toDate
     * @return void
     * @throws \Exception
     */
    private function makeUserWorkScheduleSets(
        ObjectManager $manager,
        string $referenceName,
        User $owner,
        WorkScheduleProfile $workScheduleProfile,
        int $status,
        string $fromDate,
        string $toDate
    ): void {
        $userWorkSchedule = $this->makeUserWorkSchedule(
            $manager,
            $referenceName,
            $owner,
            $workScheduleProfile,
            $status,
            $fromDate,
            $toDate
        );
    }

    /**
     * @param ObjectManager $manager
     * @param string $referenceName
     * @param User $owner
     * @param WorkScheduleProfile $workScheduleProfile
     * @param int $status
     * @param string $fromDate
     * @param string $toDate
     * @return UserWorkSchedule
     * @throws \Exception
     */
    private function makeUserWorkSchedule(
        ObjectManager $manager,
        string $referenceName,
        User $owner,
        WorkScheduleProfile $workScheduleProfile,
        int $status,
        string $fromDate,
        string $toDate
    ): UserWorkSchedule {
        $userWorkSchedule = new UserWorkSchedule();
        $userWorkSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus($status)
            ->setFromDate(new \DateTime($fromDate))
            ->setToDate(new \DateTime($toDate));

        $manager->persist($userWorkSchedule);
        $this->addReference($referenceName, $userWorkSchedule);
        return $userWorkSchedule;
    }

    /**
     * @param ObjectManager $manager
     * @param DayDefinition $dayDefinition
     * @param WorkScheduleProfile|null $userWorkScheduleProfile
     * @param UserWorkSchedule $userWorkSchedule
     * @return UserWorkScheduleDay
     */
    private function makeUserWorkScheduleDay(
        ObjectManager $manager,
        DayDefinition $dayDefinition,
        ?WorkScheduleProfile $userWorkScheduleProfile,
        UserWorkSchedule $userWorkSchedule
    ): UserWorkScheduleDay {
        $userWorkScheduleDay = new UserWorkScheduleDay();
        $userWorkScheduleDay->setDayDefinition($dayDefinition)
            ->setDailyWorkingTime($userWorkScheduleProfile->getDailyWorkingTime())
            ->setWorkingDay($dayDefinition->getWorkingDay())
            ->setDayStartTimeFrom($userWorkScheduleProfile->getDayStartTimeFrom())
            ->setDayStartTimeTo($userWorkScheduleProfile->getDayStartTimeTo())
            ->setDayEndTimeFrom($userWorkScheduleProfile->getDayStartTimeFrom())
            ->setDayEndTimeTo($userWorkScheduleProfile->getDayStartTimeTo());

        $userWorkSchedule->addUserWorkScheduleDay($userWorkScheduleDay);
        $manager->persist($userWorkScheduleDay);
        return $userWorkScheduleDay;
    }
}
