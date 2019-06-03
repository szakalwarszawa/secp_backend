<?php

namespace App\DataFixtures;

use App\Entity\Section;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var \Faker\Factory
     */
    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = \Faker\Factory::create('pl_PL');
    }

    public function getDependencies()
    {
        return array(
            DepartmentFixtures::class,
            SectionFixtures::class,
        );
    }

    public function load(ObjectManager $manager)
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
        $this->addReference('user-admin', $user);

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

            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    'test'
                )
            );

            $manager->persist($user);

            $this->setReference("user_$i", $user);
        }

        $manager->flush();
    }
}
