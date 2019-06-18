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
        $department = $this->makeDepartment(
            $manager,
            'department_admin',
            'Biuro Informatyki',
            'BI',
            true
        );

        for ($i = 0; $i < 20; $i++) {
            $department = $this->makeDepartment(
                $manager,
                "department_$i",
                $this->faker->realText(),
                $this->faker->realText(20),
                $this->faker->boolean(80)
            );
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string $referenceName
     * @param string $name
     * @param string $shortName
     * @param bool $active
     * @return Department
     */
    private function makeDepartment(
        ObjectManager $manager,
        string $referenceName,
        string $name,
        string $shortName,
        bool $active
    ): Department {
        $department = new Department();
        $department->setName($name)
            ->setShortName($shortName)
            ->setActive($active);

        $manager->persist($department);

        $this->setReference($referenceName, $department);

        return $department;
    }
}
