<?php

namespace App\DataFixtures;

use App\Entity\Department;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DepartmentFixtures extends Fixture //implements DependentFixtureInterface
{
    /**
     * @var \Faker\Factory
     */
    private $faker;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

//    public function getDependencies()
//    {
//        return array();
//    }

    public function load(ObjectManager $manager)
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
