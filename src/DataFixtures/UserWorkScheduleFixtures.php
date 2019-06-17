<?php

namespace App\DataFixtures;

use App\Entity\DayDefinition;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class UserWorkScheduleFixtures extends Fixture implements DependentFixtureInterface
{
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

    public function load(ObjectManager $manager)
    {
        $userWorkSchedule = new UserWorkSchedule();
        $userWorkSchedule->setOwner($this->getReference('user_admin'));
        $userWorkSchedule->setWorkScheduleProfile($this->getReference('work_schedule_profile_0'));
        $userWorkSchedule->setStatus(UserWorkSchedule::STATUS_HR_ACCEPT);
        $userWorkSchedule->setFromDate(new \DateTime('2019-05-01'));
        $userWorkSchedule->setToDate(new \DateTime('2019-08-31'));
        $manager->persist($userWorkSchedule);
        $this->addReference('user_work_schedule_admin_hr', $userWorkSchedule);

        $dayDefinitions = $manager->getRepository(DayDefinition::class)->findAllBetweenDate('2019-05-01', '2019-08-31');
        /* @var $dayDefinitions DayDefinition[] */
        foreach ($dayDefinitions as $dayDefinition) {
            $userWorkScheduleProfile = $userWorkSchedule->getWorkScheduleProfile();

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
        }

        $userWorkSchedule = new UserWorkSchedule();
        $userWorkSchedule->setOwner($this->getReference('user_admin'));
        $userWorkSchedule->setWorkScheduleProfile($this->getReference('work_schedule_profile_0'));
        $userWorkSchedule->setStatus(UserWorkSchedule::STATUS_OWNER_EDIT);
        $userWorkSchedule->setFromDate(new \DateTime('2019-07-01'));
        $userWorkSchedule->setToDate(new \DateTime('2019-08-31'));
        $manager->persist($userWorkSchedule);
        $this->addReference('user_work_schedule_admin_edit', $userWorkSchedule);

        $manager->flush();
    }
}
