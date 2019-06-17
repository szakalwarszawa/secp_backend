<?php

namespace App\DataFixtures;

use App\Entity\DayDefinition;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DayDefinitionFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return array(
            UserFixtures::class,
        );
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 1000; $i++) {
            $day = $this->createDayDefinitionForDay(new \DateTime("2019-05-01 +$i days"));
            $manager->persist($day);

            $this->setReference("day_definition_$i", $day);
        }
        $manager->flush();
    }

    /**
     * @param \DateTime $day
     * @return DayDefinition
     */
    private function createDayDefinitionForDay(\DateTime $day): DayDefinition
    {
        $dayDefinition = new DayDefinition();
        $dayDefinition->setId($day->format('Y-m-d'));
        $dayDefinition->setWorkingDay($this->getWorkingDay($day) === null);
        $dayDefinition->setNotice($this->getWorkingDay($day));

        return $dayDefinition;
    }

    /**
     * @param \DateTime $day
     * @return string|null
     */
    private function getWorkingDay(\DateTime $day): ?string
    {
        $bankHoliday = ['01-01', '05-01', '05-03', '08-15', '11-01', '11-11', '12-25', '12-26'];

        $easter = date('m-d', easter_date($day->format('Y')));
        $date = strtotime($day->format('Y') . '-' . $easter);
        $easterSec = date('m-d', strtotime('+1 day', $date));
        $cc = date('m-d', strtotime('+60 days', $date));
        $bankHoliday[] = $easter;
        $bankHoliday[] = $easterSec;
        $bankHoliday[] = $cc;

        if (in_array($day->format('m-d'), $bankHoliday, true)) {
            return 'Święto';
        }

        if ($day->format('w') === '0') {
            return 'Niedziela';
        }

        if ($day->format('w') === '6') {
            return 'Sobota';
        }

        return null;
    }
}
