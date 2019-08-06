<?php

namespace App\DataFixtures;

use App\Entity\Department;
use App\Entity\Section;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as Faker;

/**
 * Class SectionFixtures
 * @package App\DataFixtures
 */
class SectionFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var string
     */
    const REF_BI_SECTION = 'section_bi';

    /**
     * @var string
     */
    const REF_BP_SECION = 'section_bp';

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
        $this->makeSection(
            $manager,
            self::REF_BI_SECTION,
            'Sekcja Rozwoju Oprogramowania',
            true,
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_ADMIN)
        );

        $this->makeSection(
            $manager,
            self::REF_BP_SECION,
            'Sekcja Prezesowa',
            true,
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_BP)
        );

        for ($i = 0; $i < 100; $i++) {
            $this->makeSection(
                $manager,
                "section_$i",
                $this->faker->realText(30),
                $this->faker->boolean(80),
                $this->getReference('department_' . rand(0, 16))
            );
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string $referenceName
     * @param string $name
     * @param bool $active
     * @param Department $department
     * @return Section
     */
    private function makeSection(
        ObjectManager $manager,
        string $referenceName,
        string $name,
        bool $active,
        Department $department
    ): Section {
        $section = new Section();
        $section->setName($name)
            ->setActive($active);

        $manager->persist($section);
        $section->setDepartment($department);

        $this->setReference($referenceName, $section);

        return $section;
    }
}
