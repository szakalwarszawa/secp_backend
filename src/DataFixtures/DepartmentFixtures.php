<?php

namespace App\DataFixtures;

use App\Entity\Department;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as Faker;

class DepartmentFixtures extends Fixture
{
    /**
     * @var Faker
     */
    private $faker;

    /**
     * DepartmentFixtures constructor.
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
        $department = new Department();
        $department->setName('Biuro Informatyki');
        $department->setShortName('BI');
        $department->setActive(true);
        $manager->persist($department);

        $this->setReference('department_admin', $department);

        for ($i = 0; $i < 20; $i++) {
            $department = new Department();
            $department->setName($this->faker->realText());
            $department->setShortName($this->faker->realText(20));
            $department->setActive($this->faker->boolean(80));
            $manager->persist($department);

            $this->setReference("department_$i", $department);
        }

        $manager->flush();
    }
}
