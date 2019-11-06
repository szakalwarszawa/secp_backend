<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\WorkScheduleProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as Faker;

/**
 * Class WorkScheduleProfileFixtures
 */
class WorkScheduleProfileFixtures extends Fixture
{
    /**
     * @var array
     */
    private $properties = [
        'dailyWorkingTime' => [
            'visible' => true,
        ],
        'dayStartTimeFrom' => [
            'visible' => true,
        ],
        'dayStartTimeTo' => [
            'visible' => true,
        ],
        'dayEndTimeFrom' => [
            'visible' => true,
        ],
        'dayEndTimeTo' => [
            'visible' => true,
        ],
    ];

    /**
     * @var array
     */
    private $profiles = [
        ['Domyślny', '08:30', '08:30', '16:30', '16:30', 8.00, 'FFFFF'],
        ['Indywidualny', '08:30', '08:30', '16:30', '16:30', 8.00, 'TFTFT'],
        ['Ruchomy', '08:00', '10:00', '16:00', '18:00', 8.00, 'TTTTT'],
        ['Harmonogram', '08:30', '08:30', '16:30', '16:30', 8.00, 'TFFFF'],
        ['Brak', '08:30', '08:30', '16:30', '16:30', 8.00, 'FFFFF'],
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
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $i = 0;
        foreach ($this->profiles as $profile) {
            $workScheduleProfile = new WorkScheduleProfile();
            $workScheduleProfile->setName($profile[0])
                ->setDayStartTimeFrom($profile[1])
                ->setDayStartTimeTo($profile[2])
                ->setDayEndTimeFrom($profile[3])
                ->setDayEndTimeTo($profile[4])
                ->setDailyWorkingTime($profile[5])
                ->setProperties($this->prepareProperties($profile[6]))
            ;

            $manager->persist($workScheduleProfile);

            $this->setReference('work_schedule_profile_' . $i++, $workScheduleProfile);
        }

        $manager->flush();
    }

    /**
     * @param string $settings
     *
     * @return array
     */
    private function prepareProperties(string $settings): array
    {
        $fields = [
            'dailyWorkingTime',
            'dayStartTimeFrom',
            'dayStartTimeTo',
            'dayEndTimeFrom',
            'dayEndTimeTo',
        ];

        $properties = $this->properties;

        foreach ($fields as $idx => $field) {
            $properties[$field] = ['visible' => $settings[$idx] === 'T'];
        }

        return $properties;
    }
}
