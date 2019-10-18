<?php
declare(strict_types=1);

namespace App\Tests\Entity;

use App\DataFixtures\RoleFixtures;
use App\Entity\Department;
use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class UserTest extends AbstractWebTestCase
{
    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testUserAssignDepartment(): void
    {
        $user = $this->getEntityFromReference('user_admin');
        /* @var $user User */
        $this->assertInstanceOf(User::class, $user);

        $department = $this->getEntityFromReference('department_1');
        /* @var $department Department */
        $this->assertInstanceOf(Department::class, $department);

        $user->setDepartment($department);
        $this->assertEquals($department, $user->getDepartment());

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testSomething(): void
    {
        $department = new Department();
        $department
            ->setName('department 1')
            ->setShortName('dep 1')
            ->setActive(true);
        $this->entityManager->persist($department);

        $user = new User();
        $user
            ->setDepartment($this->getEntityFromReference('department_1'))
            ->setDefaultWorkScheduleProfile($this->getEntityFromReference('work_schedule_profile_0'))
            ->setSamAccountName('sam_account_name_1')
            ->setUsername('user_name_1')
            ->setEmail('user_email_1@example.com')
            ->setFirstName('user_first_name')
            ->setLastName('user_last_name')
            ->setRoles([RoleFixtures::ROLE_ADMIN])
            ->setPlainPassword('test');

        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
        $this->assertTrue(true);
    }
}
