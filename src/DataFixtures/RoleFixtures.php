<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class RoleFixtures
 */
class RoleFixtures extends Fixture
{
    /**
     * @var string
     */
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @var string
     */
    public const ROLE_SUPERVISOR = 'ROLE_SUPERVISOR';

    /**
     * @var string
     */
    public const ROLE_HR = 'ROLE_HR';

    /**
     * @var string
     */
    public const ROLE_DEPARTMENT_MANAGER = 'ROLE_DEPARTMENT_MANAGER';

    /**
     * @var string
     */
    public const ROLE_SECTION_MANAGER = 'ROLE_SECTION_MANAGER';

    /**
     * @var string
     */
    public const ROLE_SECRETARY = 'ROLE_SECRETARY';

    /**
     * @var string
     */
    public const ROLE_USER = 'ROLE_USER';

    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $roles = [
            'ROLE_HR',
            'ROLE_SECTION_MANAGER',
            'ROLE_DEPARTMENT_MANAGER',
            'ROLE_SECRETARY',
            'ROLE_ADMIN',
            'ROLE_SUPERVISOR',
            'ROLE_USER',
        ];

        foreach ($roles as $role) {
            $roleObject = new Role();
            $roleObject->setName($role);
            $manager->persist($roleObject);

            $this->setReference($role, $roleObject);
        }

        $manager->flush();
    }
}
