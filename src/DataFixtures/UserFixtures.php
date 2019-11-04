<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Department;
use App\Entity\User;
use App\Entity\WorkScheduleProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Faker\Factory as Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserFixtures
 */
class UserFixtures extends Fixture implements DependentFixtureInterface
{

    /**
     * @var string
     */
    public const REF_USER_ADMIN = 'user_admin';

    /**
     * @var string
     */
    public const REF_USER_MANAGER = 'user_manager';

    /**
     * @var string
     */
    public const REF_USER_SECTION_MANAGER = 'user_section_manager';

    /**
     * @var string
     */
    public const REF_USER_SECRETARY = 'user_secretary';

    /**
     * @var string
     */
    public const REF_USER_USER = 'user_user';

    /**
     * @var string
     */
    public const REF_USER_HR_MANAGER = 'user_hr_manager';

    /**
     * @var string
     */
    public const REF_USER_HR_SECTION_MANAGER = 'user_hr_section_manager';

    /**
     * @var string
     */
    public const REF_USER_HR_SECRETARY = 'user_hr_secretary';

    /**
     * @var string
     */
    public const REF_USER_HR_USER = 'user_hr_user';

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
     *
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
     *
     * @return void
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->makeFixedUserBi($manager);
        $this->makeFixedUserHr($manager);

        for ($i = 0; $i < 100; $i++) {
            $firstName = $this->faker->firstName();
            $lastName = $this->faker->lastName;
            $username = strtolower(sprintf('%s_%s_%s', $lastName, $firstName, $this->faker->randomNumber(3)));

            $user = $this->makeUser(
                $manager,
                "user_$i",
                $username,
                $username,
                $firstName,
                $lastName,
                [RoleFixtures::ROLE_USER],
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
     * @param string $firstName
     * @param string $lastName
     * @param array $roles
     * @param Department $department
     * @param WorkScheduleProfile $defaultWorkScheduleProfile
     *
     * @return User
     */
    private function makeUser(
        ObjectManager $manager,
        string $referenceName,
        string $username,
        string $samAccountName,
        string $firstName,
        string $lastName,
        array $roles,
        Department $department,
        WorkScheduleProfile $defaultWorkScheduleProfile
    ): User {
        $email = $username . '@' . $this->faker->safeEmailDomain;
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

    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    private function makeFixedUserBi(ObjectManager $manager): void
    {
        $user = $this->makeUser(
            $manager,
            self::REF_USER_ADMIN,
            'admin',
            'admin',
            'Adam',
            'Admin',
            [RoleFixtures::ROLE_ADMIN],
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_ADMIN),
            $this->getReference('work_schedule_profile_0')
        );
        $manager->flush();

        $user->getDepartment()->addUser($user);
        $user->getDepartment()->addManager($user);
        $manager->flush();

        $user = $this->makeUser(
            $manager,
            self::REF_USER_MANAGER,
            'manager',
            'manager',
            'Mariusz',
            'Manager',
            [RoleFixtures::ROLE_USER, RoleFixtures::ROLE_DEPARTMENT_MANAGER],
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_ADMIN),
            $this->getReference('work_schedule_profile_1')
        );
        $manager->flush();

        $user->getDepartment()->addUser($user);
        $user->getDepartment()->addManager($user);
        $manager->flush();

        $user = $this->makeUser(
            $manager,
            self::REF_USER_SECTION_MANAGER,
            'section',
            'section',
            'Stanisław',
            'Section',
            [RoleFixtures::ROLE_USER, RoleFixtures::ROLE_SECTION_MANAGER],
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_ADMIN),
            $this->getReference('work_schedule_profile_4')
        );
        $user->setSection($this->getReference(SectionFixtures::REF_BI_SECTION));
        $manager->persist($user);
        $manager->flush();

        $user->getDepartment()->addUser($user);
        $user->getSection()->addUser($user);
        $user->getSection()->addManager($user);
        $manager->flush();

        $user = $this->makeUser(
            $manager,
            self::REF_USER_SECRETARY,
            'secretary',
            'secretary',
            'Sylwia',
            'Secretary',
            [RoleFixtures::ROLE_USER, RoleFixtures::ROLE_SECRETARY],
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_ADMIN),
            $this->getReference('work_schedule_profile_2')
        );
        $manager->flush();

        $user->getDepartment()->addUser($user);
        $manager->flush();

        $user = $this->makeUser(
            $manager,
            self::REF_USER_USER,
            'user',
            'user',
            'Urszula',
            'User',
            [RoleFixtures::ROLE_USER],
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_ADMIN),
            $this->getReference('work_schedule_profile_3')
        );
        $user->setSection($this->getReference(SectionFixtures::REF_BI_SECTION));
        $manager->persist($user);
        $manager->flush();

        $user->getDepartment()->addUser($user);
        $user->getSection()->addUser($user);
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     *
     * /**
     * @var string@return void
     *
     */
    private function makeFixedUserHr(ObjectManager $manager): void
    {
        $user = $this->makeUser(
            $manager,
            self::REF_USER_HR_MANAGER,
            'hr_manager',
            'hr_manager',
            'Monika',
            'Hr-Manger',
            [RoleFixtures::ROLE_USER, RoleFixtures::ROLE_HR],
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_HR),
            $this->getReference('work_schedule_profile_0')
        );
        $manager->flush();

        $user->getDepartment()->addUser($user);
        $user->getDepartment()->addManager($user);
        $manager->flush();

        $user = $this->makeUser(
            $manager,
            self::REF_USER_HR_SECTION_MANAGER,
            'hr_section',
            'hr_section',
            'Halina',
            'Hr-Section',
            [RoleFixtures::ROLE_USER, RoleFixtures::ROLE_SECTION_MANAGER, RoleFixtures::ROLE_HR],
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_HR),
            $this->getReference('work_schedule_profile_0')
        );
        $user->setSection($this->getReference(SectionFixtures::REF_HR_SECTION));
        $manager->persist($user);
        $manager->flush();

        $user->getDepartment()->addUser($user);
        $user->getSection()->addUser($user);
        $user->getSection()->addManager($user);
        $manager->flush();

        $user = $this->makeUser(
            $manager,
            self::REF_USER_HR_SECRETARY,
            'hr_secretary',
            'hr_secretary',
            'Stefania',
            'Hr_secretary',
            [RoleFixtures::ROLE_USER, RoleFixtures::ROLE_SECRETARY],
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_HR),
            $this->getReference('work_schedule_profile_1')
        );
        $manager->flush();

        $user->getDepartment()->addUser($user);
        $manager->flush();

        $user = $this->makeUser(
            $manager,
            self::REF_USER_HR_USER,
            'hr_user',
            'hr_user',
            'Honorata',
            'Hr-User',
            [RoleFixtures::ROLE_USER],
            $this->getReference(DepartmentFixtures::REF_DEPARTMENT_HR, RoleFixtures::ROLE_HR),
            $this->getReference('work_schedule_profile_3')
        );
        $user->setSection($this->getReference(SectionFixtures::REF_HR_SECTION));
        $manager->persist($user);
        $manager->flush();

        $user->getDepartment()->addUser($user);
        $user->getSection()->addUser($user);
        $manager->flush();
    }
}
