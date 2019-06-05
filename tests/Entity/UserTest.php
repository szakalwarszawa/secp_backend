<?php

namespace App\Tests\Entity;

use App\DataFixtures\DepartmentFixtures;
use App\DataFixtures\SectionFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Department;
use App\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Liip\FunctionalTestBundle\Test\WebTestCase;


/**
 * @method entityManager(string $class)
 */
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

    private function getEntityFromReference($referenceName): ?object
    {
        if (!$this->referenceRepository->hasReference($referenceName)) {
            return null;
        }

        $reference = $this->referenceRepository->getReference($referenceName);

        return $this->entityManager->getRepository(get_class($reference))->find($reference->getId());
    }

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

        $user->setTitle('test');

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
        $department->setName('department 1');
        $department->setShortName('dep 1');
        $department->setActive(true);
        $this->entityManager->persist($department);

        $user = new User();
        $user->setDepartment($this->getEntityFromReference('department_0'));
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
