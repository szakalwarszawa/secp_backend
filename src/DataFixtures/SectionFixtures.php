<?php

namespace App\DataFixtures;

use App\Entity\Section;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SectionFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var \Faker\Factory
     */
    private $faker;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

    public function getDependencies()
    {
        return array(
            DepartmentFixtures::class,
        );
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 30; $i++) {
            $section = new Section();
            $section->setName($this->faker->realText());
            $section->setActive($this->faker->boolean(80));
            $section->setDepartment($this->getReference('department_' . rand(0, 16)));
            $manager->persist($section);

            $this->setReference("section_$i", $section);
        }

        $manager->flush();
    }
}
