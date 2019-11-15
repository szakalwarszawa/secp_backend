<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class UserSystemFixtures
 */
class UserSystemFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $this->makeSystemWorkScheduleProfile($manager);
        $this->makeSystemDepartment($manager);
        $this->makeSystemUser($manager);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    private function makeSystemWorkScheduleProfile(ObjectManager $manager): void
    {
        $tableName = 'dictionary.work_schedule_profiles';
        if ($manager->getConnection()->getDatabasePlatform()->getName() === 'sqlite') {
            $tableName = 'dictionary__work_schedule_profiles';
        }

        $manager->getConnection()->exec(<<<SQL
INSERT INTO $tableName (id, name, notice) 
VALUES (0, 'System', 'SYSTEM')
SQL
        );
    }

    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    private function makeSystemDepartment(ObjectManager $manager): void
    {
        $manager->getConnection()->exec(<<<SQL
INSERT INTO departments (id, name, short_name, active) 
VALUES (0, 'System', 'SYSTEM', true )
SQL
        );
    }

    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    private function makeSystemUser(ObjectManager $manager): void
    {
        $randomPassword = bin2hex(openssl_random_pseudo_bytes(100));
        $systemUsername = User::SYSTEM_USERNAME;

        $manager->getConnection()->exec(<<<SQL
INSERT INTO users (
        id, 
        email, 
        roles, 
        password,
        department_id, 
        section_id, 
        sam_account_name, 
        username, 
        first_name, 
        last_name, 
        default_work_schedule_profile_id
    ) VALUES (
        0, 
        'system@system',
        'ROLE_ADMIN', 
        '{$randomPassword}',
        0,
        null, 
        '{$systemUsername}',
        '{$systemUsername}',
        '{$systemUsername}',
        '{$systemUsername}',
        0
    )
SQL
        );
    }
}
