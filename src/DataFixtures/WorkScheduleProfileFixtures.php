<?php

namespace App\DataFixtures;

use App\Entity\WorkScheduleProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as Faker;

class WorkScheduleProfileFixtures extends Fixture
{
    private $profiles = [
        'Domyślny',
        'Indywidualny',
        'Ruchomy',
        'Harmonogram',
        'Brak',
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
            $workScheduleProfile->setName($profile);
            $manager->persist($workScheduleProfile);

            $this->setReference('work_schedule_profile_' . $i++, $workScheduleProfile);
        }

        $manager->flush();
    }
}
