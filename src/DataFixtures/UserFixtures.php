<?php

namespace App\DataFixtures;

use App\Entity\Department;
use App\Entity\User;
use App\Entity\WorkScheduleProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory as Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserFixtures
 * @package App\DataFixtures
 */
class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const REF_USER_ADMIN = 'user_admin';
    public const REF_USER_MANAGER = 'user_manager';
    public const REF_USER_USER = 'user_user';

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
            WorkScheduleProfileFixtures::class,
        );
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $user = $this->makeUser(
            $manager,
            self::REF_USER_ADMIN,
            'admin',
            'admin',
            'admin@quest.info.pl',
            'Adam',
            'Admin',
            [User::ROLE_ADMIN],
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_ADMIN),
            $this->getReference('work_schedule_profile_0')
        );

        $manager->flush();

        $user->getDepartment()->addUser($user);
        $user->getDepartment()->addManager($user);

        $user = $this->makeUser(
            $manager,
            self::REF_USER_MANAGER,
            'manager',
            'manager',
            'manager@quest.info.pl',
            'Mariusz',
            'Manager',
            [User::ROLE_USER],
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_ADMIN),
            $this->getReference('work_schedule_profile_1')
        );

        $manager->flush();

        $user->getDepartment()->addUser($user);
        $user->getDepartment()->addManager($user);

        $manager->flush();

        $user = $this->makeUser(
            $manager,
            self::REF_USER_USER,
            'user',
            'user',
            'user@quest.info.pl',
            'Urszula',
            'User',
            [User::ROLE_USER],
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_ADMIN),
            $this->getReference('work_schedule_profile_3')
        );

        $manager->flush();

        $user->getDepartment()->addUser($user);

        $manager->flush();

        for ($i = 0; $i < 100; $i++) {
            $firstName = $this->faker->firstName();
            $lastName = $this->faker->lastName;
            $username = strtolower(sprintf('%s_%s_%s', $lastName, $firstName, $this->faker->randomNumber(3)));

            $user = $this->makeUser(
                $manager,
                "user_$i",
                $username,
                $username,
                $username . '@' . $this->faker->safeEmailDomain,
                $firstName,
                $lastName,
                [User::ROLE_USER],
                $this->getReference('department_' . random_int(0, 19)),
                $this->getReference('work_schedule_profile_0')
            );

            $departmentSections = $user->getDepartment()->getSections();
            if ($departmentSections !== null && $departmentSections->count() > 0) {
                $section = $departmentSections->get(
                    $departmentSections->getKeys()[random_int(0, count($departmentSections->getKeys()) - 1)]
                );
                $user->setSection($section);
            }

            if ($this->faker->boolean(20)) {
                $user->getDepartment()->addManager($user);
            } elseif ($this->faker->boolean(20) && $user->getSection() !== null) {
                $user->getSection()->addManager($user);
            }
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string $referenceName
     * @param string $username
     * @param string $samAccountName
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param array $roles
     * @param Department $department
     * @param WorkScheduleProfile $defaultWorkScheduleProfile
     * @return User
     */
    private function makeUser(
        ObjectManager $manager,
        string $referenceName,
        string $username,
        string $samAccountName,
        string $email,
        string $firstName,
        string $lastName,
        array $roles,
        Department $department,
        WorkScheduleProfile $defaultWorkScheduleProfile
    ): User {
        $user = new User();
        $user->setUsername($username)
            ->setSamAccountName($samAccountName)
            ->setEmail($email)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRoles($roles)
            ->setDepartment($department)
            ->setDefaultWorkScheduleProfile($defaultWorkScheduleProfile)
            ->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    'test'
                )
            );

        $this->addReference($referenceName, $user);
        $manager->persist($user);
        return $user;
    }
}
