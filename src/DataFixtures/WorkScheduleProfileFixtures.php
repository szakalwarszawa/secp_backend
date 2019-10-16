<?php

namespace App\DataFixtures;

use App\Entity\WorkScheduleProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as Faker;

/**
 * Class WorkScheduleProfileFixtures
 * @package App\DataFixtures
 */
class WorkScheduleProfileFixtures extends Fixture
{
    /**
     * @var array
     */
    private $properties = [
        'dayStartTimeFromDate' => [
            'visible' => true,
        ],
        'dayStartTimeToDate' => [
            'visible' => true,
        ],
        'dayEndTimeFromDate' => [
            'visible' => true,
        ],
        'dayEndTimeToDate' => [
            'visible' => true,
        ],
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
        ['Harmonogram', '08:30', '08:30', '16:30', '16:30', 8.00, 'TFTFT'],
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
            'dayStartTimeFrom',
            'dayStartTimeTo',
            'dayEndTimeFrom',
            'dayEndTimeTo',
            'dailyWorkingTime',
        ];

        $properties = $this->properties;

        foreach ($fields as $idx => $field) {
            $properties[$field] = ['visible' => $settings[$idx] === 'T'];
        }

        return $properties;
    }
}
