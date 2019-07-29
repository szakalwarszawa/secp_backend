<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var Faker
     */
    private $faker;

    /**
     * UserFixtures constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = Faker::create('pl_PL');
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return array(
            DepartmentFixtures::class,
            SectionFixtures::class,
        );
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setSamAccountName('admin');
        $user->setEmail('admin@quest.info.pl');
        $user->setFirstName('Adam');
        $user->setLastName('Admin');
        $user->setRoles([User::ROLE_ADMIN]);
        $user->setDepartment($this->getReference('department_admin'));

        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                'test'
            )
        );

        $manager->persist($user);
        $manager->flush();
        $this->addReference('user_admin', $user);

        $user->getDepartment()->addUser($user);
        $user->getDepartment()->addManager($user);
        $manager->persist($user);
        $manager->flush();

        for ($i = 0; $i < 100; $i++) {
            $firstName = $this->faker->firstName();
            $lastName = $this->faker->lastName;
            $user = new User();
            $user->setUsername(strtolower(sprintf('%s_%s', $lastName, $firstName)));
            $user->setSamAccountName($user->getUsername());
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($user->getUsername() . '@' . $this->faker->safeEmailDomain);
            $user->setTitle($this->faker->realText(50));
            $user->setRoles([User::ROLE_USER]);
            $user->setDepartment($this->getReference('department_' . rand(0, 19)));

            $departmentSections = $user->getDepartment()->getSections();
            if ($departmentSections !== null && $departmentSections->count() > 0) {
                $section = $departmentSections->get(
                    $departmentSections->getKeys()[random_int(0, count($departmentSections->getKeys()) - 1)]
                );
                $user->setSection($section);
            }

            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    'test'
                )
            );

            if ($this->faker->boolean(20)) {
                $user->getDepartment()->addManager($user);
            } else if ($this->faker->boolean(20) && $user->getSection() !== null) {
                $user->getSection()->addManager($user);
            }

            $manager->persist($user);

            $this->setReference("user_$i", $user);
        }

        $manager->flush();
    }
}
