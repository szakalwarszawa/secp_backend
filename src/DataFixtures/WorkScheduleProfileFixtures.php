<?php

namespace App\DataFixtures;

use App\Entity\WorkScheduleProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as Faker;

class WorkScheduleProfileFixtures extends Fixture
{
    private $profiles = [
        ['Domyślny', '08:30', '08:30', '16:30', '16:30', 8.00],
        ['Indywidualny', '08:30', '08:30', '16:30', '16:30', 8.00],
        ['Ruchomy', '08:00', '10:00', '16:00', '18:00', 8.00],
        ['Harmonogram', '08:30', '08:30', '16:30', '16:30', 8.00],
        ['Brak', '08:30', '08:30', '16:30', '16:30', 8.00],
    ];

    /**
     * @var Faker
     */
    private $faker;

    /**
     * SectionFixtures constructor.
     */
    public function __construct()
    {
        $this->faker = Faker::create('pl_PL');
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $i = 0;
        foreach ($this->profiles as $profile) {
            $workScheduleProfile = new WorkScheduleProfile();
            $workScheduleProfile->setName($profile[0]);
            $workScheduleProfile->setDayStartTimeFrom($profile[1]);
            $workScheduleProfile->setDayStartTimeTo($profile[2]);
            $workScheduleProfile->setDayEndTimeFrom($profile[3]);
            $workScheduleProfile->setDayEndTimeTo($profile[4]);
            $workScheduleProfile->setDailyWorkingTime($profile[5]);
            $manager->persist($workScheduleProfile);

            $this->setReference('work_schedule_profile_' . $i++, $workScheduleProfile);
        }

        $manager->flush();
    }
}
