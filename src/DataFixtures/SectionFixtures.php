<?php

namespace App\DataFixtures;

use App\Entity\Section;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as Faker;

class SectionFixtures extends Fixture implements DependentFixtureInterface
{
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
     * @return array
     */
    public function getDependencies(): array
    {
        return array(
            DepartmentFixtures::class,
        );
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
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
