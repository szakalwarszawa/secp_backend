<?php

namespace App\Tests;

use App\DataFixtures\AbsenceTypeFixtures;
use App\DataFixtures\DayDefinitionFixtures;
use App\DataFixtures\DepartmentFixtures;
use App\DataFixtures\PresenceTypeFixtures;
use App\DataFixtures\SectionFixtures;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\UserTimesheetFixtures;
use App\DataFixtures\UserWorkScheduleFixtures;
use App\DataFixtures\WorkScheduleProfileFixtures;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class Fixtures extends WebTestCase
{
    /**
     * @return ReferenceRepository
     */
    public function getFixtures(): ReferenceRepository
    {
        return $this->loadFixtures(
            [
                WorkScheduleProfileFixtures::class,
                PresenceTypeFixtures::class,
                AbsenceTypeFixtures::class,
                DepartmentFixtures::class,
                SectionFixtures::class,
                UserFixtures::class,
                DayDefinitionFixtures::class,
                UserWorkScheduleFixtures::class,
                UserTimesheetFixtures::class,
            ]
        )->getReferenceRepository();
    }
}
