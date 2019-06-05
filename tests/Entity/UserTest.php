<?php

namespace App\Tests\Entity;

use App\DataFixtures\DepartmentFixtures;
use App\DataFixtures\SectionFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Department;
use App\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Liip\FunctionalTestBundle\Test\WebTestCase;


class UserTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ReferenceRepository
     */
    private $referenceRepository;

    /**
     * @throws ToolsException
     */
    protected function setUp(): void
    {

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropDatabase();
        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }
        $this->postFixtureSetup();

        $fixtures = array(
            DepartmentFixtures::class,
            SectionFixtures::class,
            UserFixtures::class,
        );
        $this->referenceRepository = $this->loadFixtures($fixtures)->getReferenceRepository();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testSomething()
    {
        $department = new Department();
        $department->setName('department 1');
        $department->setShortName('dep 1');
        $department->setActive(true);
        $this->entityManager->persist($department);

//        $this->referenceRepository->addReference('department_1', $department);

        $user = new User();
        $user->setDepartment($department);
        $user->setSamAccountName('sam_account_name_1');
        $user->setUsername('user_name_1');
        $user->setEmail('user_email_1@example.com');
        $user->setFirstName('user_first_name');
        $user->setLastName('user_last_name');
        $user->setRoles([User::ROLE_ADMIN]);
        $user->setPlainPassword('test');

        $this->entityManager->persist($user);
        $this->entityManager->flush($user);
        $this->assertTrue(true);
    }
}
