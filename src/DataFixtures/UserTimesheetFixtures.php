<?php

namespace App\DataFixtures;

use App\Entity\AbsenceType;
use App\Entity\PresenceType;
use App\Entity\User;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetDay;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class UserTimesheetFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return array(
            AbsenceTypeFixtures::class,
            PresenceTypeFixtures::class,
            UserFixtures::class,
            DayDefinitionFixtures::class,
            WorkScheduleProfileFixtures::class,
            UserWorkScheduleFixtures::class,
        );
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $this->makeUserTimesheetSets(
            $manager,
            'user_timesheet_admin_edit',
            $this->getReference('user_admin'),
            '2019-06',
            UserTimesheet::STATUS_OWNER_EDIT,
            $this->getReference('user_work_schedule_admin_hr'),
            ['2019-06-03', '2019-06-04', '2019-06-05', '2019-06-06', '2019-06-07']
        );

        $this->makeUserTimesheetSets(
            $manager,
            'user_timesheet_manager_hr',
            $this->getReference('user_manager'),
            '2019-05',
            UserTimesheet::STATUS_HR_ACCEPT,
            $this->getReference('user_work_schedule_manager_hr'),
            ['2019-05-27', '2019-05-28', '2019-05-29', '2019-05-30', '2019-05-31']
        );

        $this->makeUserTimesheetSets(
            $manager,
            'user_timesheet_manager_edit',
            $this->getReference('user_manager'),
            '2019-05',
            UserTimesheet::STATUS_OWNER_EDIT,
            $this->getReference('user_work_schedule_manager_hr'),
            ['2019-06-03', '2019-06-04', '2019-06-05', '2019-06-06', '2019-06-07']
        );

        $this->makeUserTimesheetSets(
            $manager,
            'user_timesheet_user_hr',
            $this->getReference('user_user'),
            '2019-05',
            UserTimesheet::STATUS_HR_ACCEPT,
            $this->getReference('user_work_schedule_user_hr'),
            ['2019-05-27', '2019-05-28', '2019-05-29', '2019-05-30', '2019-05-31']
        );

        $this->makeUserTimesheetSets(
            $manager,
            'user_timesheet_user_edit',
            $this->getReference('user_user'),
            '2019-06',
            UserTimesheet::STATUS_OWNER_EDIT,
            $this->getReference('user_work_schedule_user_hr'),
            ['2019-06-03', '2019-06-04', '2019-06-05', '2019-06-06', '2019-06-07']
        );

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string $referenceName
     * @param User $owner
     * @param string $period
     * @param int $status
     * @param UserWorkSchedule $userWorkSchedule
     * @param array $workingDays
     * @return void
     * @throws \Exception
     */
    private function makeUserTimesheetSets(
        ObjectManager $manager,
        string $referenceName,
        User $owner,
        string $period,
        int $status,
        UserWorkSchedule $userWorkSchedule,
        array $workingDays
    ): void {
        $userTimesheet = $this->makeUserTimesheet(
            $manager,
            $referenceName,
            $owner,
            $period,
            $status
        );

        foreach ($workingDays as $workingDay) {
            $userWorkScheduleDay = $manager->getRepository(UserWorkScheduleDay::class)
                ->findOneBy(
                    [
                        'userWorkSchedule' => $userWorkSchedule->getId(),
                        'dayDefinition' => $workingDay
                    ]
                );

            if ($userWorkScheduleDay !== null) {
                $presenceType = $this->getReference(
                    'presence_type_' . random_int(0, PresenceTypeFixtures::FIXTURES_RECORD_COUNT - 1)
                );
                $absenceType = null;
                $dayStartTime = '08:30';
                $dayEndTime = '16:30';
                $workingTime = 8.00;

                if ($presenceType->getShortName() === 'N') {
                    $absenceType = $this->getReference(
                        'absence_type_' . random_int(0, AbsenceTypeFixtures::FIXTURES_RECORD_COUNT - 1)
                    );
                    $dayStartTime = null;
                    $dayEndTime = null;
                    $workingTime = 0.00;
                }

                $this->makeUserWorkScheduleDay(
                    $manager,
                    $userTimesheet,
                    $userWorkScheduleDay,
                    $dayStartTime,
                    $dayEndTime,
                    $workingTime,
                    $presenceType,
                    $absenceType
                );
            }
        }
    }

    /**
     * @param ObjectManager $manager
     * @param string $referenceName
     * @param User $owner
     * @param string $period
     * @param int $status
     * @return UserTimesheet
     */
    private function makeUserTimesheet(
        ObjectManager $manager,
        string $referenceName,
        User $owner,
        string $period,
        int $status
    ): UserTimesheet {
        $userTimesheet = new UserTimesheet();
        $userTimesheet->setOwner($owner)
            ->setPeriod($period)
            ->setStatus($status);

        $manager->persist($userTimesheet);
        $this->addReference($referenceName, $userTimesheet);
        return $userTimesheet;
    }

    /**
     * @param ObjectManager $manager
     * @param UserTimesheet $userTimesheet
     * @param UserWorkScheduleDay $userWorkScheduleDay
     * @param string|null $dayStartTime
     * @param string|null $dayEndTime
     * @param float $workingTime
     * @param PresenceType $presenceType
     * @param AbsenceType $absenceType
     * @return UserTimesheetDay
     */
    private function makeUserWorkScheduleDay(
        ObjectManager $manager,
        UserTimesheet $userTimesheet,
        UserWorkScheduleDay $userWorkScheduleDay,
        ?string $dayStartTime,
        ?string $dayEndTime,
        float $workingTime,
        PresenceType $presenceType,
        ?AbsenceType $absenceType
    ): UserTimesheetDay {
        $userTimesheetDay = new UserTimesheetDay();
        $userTimesheetDay->setUserTimesheet($userTimesheet)
            ->setUserWorkScheduleDay($userWorkScheduleDay)
            ->setDayStartTime($dayStartTime)
            ->setDayEndTime($dayEndTime)
            ->setWorkingTime($workingTime)
            ->setPresenceType($presenceType)
            ->setAbsenceType($absenceType);

        $userTimesheet->addUserTimesheetDay($userTimesheetDay);
        $manager->persist($userTimesheetDay);
        return $userTimesheetDay;
    }
}
