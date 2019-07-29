<?php

namespace App\Tests;

use App\DataFixtures\DepartmentFixtures;
use App\DataFixtures\SectionFixtures;
use App\DataFixtures\UserFixtures;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class Fixtures extends WebTestCase
{
    /**
     * @return ReferenceRepository
     */
    public function getFixtures(): ReferenceRepository
    {
        return $this->loadFixtures([
            DepartmentFixtures::class,
            SectionFixtures::class,
            UserFixtures::class,
        ])->getReferenceRepository();
    }
}
