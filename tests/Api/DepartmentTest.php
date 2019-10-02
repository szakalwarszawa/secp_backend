<?php


namespace App\Tests\Api;

use App\Entity\Department;
use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use Exception;

class DepartmentTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetDepartments(): void
    {
        $departmentsDB = $this->entityManager->getRepository(Department::class)->findAll();
        /* @var $departmentDB Department */

        $response = $this->getActionResponse('GET', '/api/departments');
        $departmentsJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($departmentsJSON);
        $this->assertEquals(count($departmentsDB), $departmentsJSON->{'hydra:totalItems'});
    }

    /**
     * @test
     * @dataProvider apiGetDepartmentProvider
     * @param string $referenceName
     * @throws NotFoundReferencedUserException
     */
    public function apiGetDepartment($referenceName): void
    {
        $departmentDB = $this->fixtures->getReference($referenceName);
        /* @var $departmentDB Department */

        $response = $this->getActionResponse('GET', '/api/departments/' . $departmentDB->getId());
        $userJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userJSON);
        $this->assertEquals($departmentDB->getId(), $userJSON->id);
        $this->assertEquals($departmentDB->getName(), $userJSON->name);
        $this->assertEquals($departmentDB->getShortName(), $userJSON->shortName);
        $this->assertEquals($departmentDB->getActive(), $userJSON->active);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function apiGetDepartmentProvider(): array
    {
        $referenceList = [];

        for ($i = 0; $i < 10; $i++) {
            $randomDepartment = random_int(0, 19);
            $referenceList[] = ['department_' . $randomDepartment];
        }

        return $referenceList;
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiPostDepartment(): void
    {
        $payload = <<<'JSON'
{
  "name": "department_test",
  "shortName": "DT",
  "active": true
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_POST,
            '/api/departments',
            $payload,
            [],
            201
        );

        $departmentJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($departmentJSON);
        $this->assertIsNumeric($departmentJSON->id);
        $this->assertEquals('department_test', $departmentJSON->name);
        $this->assertEquals('DT', $departmentJSON->shortName);
        $this->assertEquals(true, $departmentJSON->active);

        $response = $this->getActionResponse(
            'GET',
            '/api/departments/' . $departmentJSON->id
        );

        $departmentDB = $this->entityManager->getRepository(Department::class)->find($departmentJSON->id);
        /* @var $departmentDB Department */

        $departmentResponsJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($departmentResponsJSON);
        $this->assertEquals($departmentDB->getId(), $departmentResponsJSON->id);
        $this->assertEquals($departmentDB->getName(), $departmentResponsJSON->name);
        $this->assertEquals($departmentDB->getShortName(), $departmentResponsJSON->shortName);
        $this->assertEquals($departmentDB->getActive(), $departmentResponsJSON->active);
    }

    /**
     * @test
     * @throws Exception
     */
    public function apiPutDepartment(): void
    {
        $departmentREF = $this->fixtures->getReference('department_' . random_int(0, 19));
        /* @var $departmentREF Department */

        $payload = <<<'JSON'
{
  "name": "department_test_put",
  "shortName": "DT_PUT",
  "active": false
}
JSON;

        $response = $this->getActionResponse(
            'PUT',
            '/api/departments/' . $departmentREF->getId(),
            $payload,
            [],
            200
        );
        $departmentJSON = json_decode($response->getContent(), false);

        $departmentDB = $this->entityManager->getRepository(Department::class)->find($departmentREF->getId());
        /* @var $departmentDB Department */

        $this->assertNotNull($departmentJSON);
        $this->assertEquals($departmentDB->getId(), $departmentJSON->id);
        $this->assertEquals($departmentDB->getName(), $departmentJSON->name);
        $this->assertEquals($departmentDB->getShortName(), $departmentJSON->shortName);
        $this->assertEquals($departmentDB->getActive(), $departmentJSON->active);
    }

    /**
     * @test
     * @throws Exception
     */
    public function apiPutDepartmentWithSections(): void
    {
        $departmentId = $this->fixtures->getReference('department_admin')->getId();

        $payload = <<<JSON
{
  "name": "section_test",
  "active": true,
  "department": "/api/departments/$departmentId" 
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_POST,
            '/api/sections',
            $payload,
            [],
            201
        );

        $this->assertJson($response->getContent());
        $sectionJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($sectionJSON);

        $newSectionId = $sectionJSON->id;

        $departmentREF = $this->fixtures->getReference('department_' . random_int(0, 19));
        /* @var $departmentREF Department */

        $originalSectionCount = count($departmentREF->getSections());

        $newSectionArray[] = '"/api/sections/' . $newSectionId . '"';
        foreach ($departmentREF->getSections() as $section) {
            $newSectionArray[] = sprintf('"/api/sections/%s"', $section->getId());
        }
        $newSectionList = implode(', ', $newSectionArray);

        $payload = <<<JSON
{
  "name": "department_test_put",
  "shortName": "DT_PUT",
  "active": false,
  "sections": [$newSectionList]
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_PUT,
            '/api/departments/' . $departmentREF->getId(),
            $payload,
            [],
            200
        );

        $departmentJSON = json_decode($response->getContent(), false);

        $departmentDB = $this->entityManager->getRepository(Department::class)->find($departmentJSON->id);
        /* @var $departmentDB Department */

        $this->assertCount(
            $originalSectionCount + 1,
            $departmentDB->getSections()
        );

        $this->assertNotNull($departmentJSON);
        $this->assertEquals($departmentDB->getId(), $departmentJSON->id);
        $this->assertEquals($departmentDB->getName(), $departmentJSON->name);
        $this->assertEquals($departmentDB->getShortName(), $departmentJSON->shortName);
        $this->assertEquals($departmentDB->getActive(), $departmentJSON->active);

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/departments/' . $departmentJSON->id,
            $payload,
            [],
            200
        );

        $departmentJSON = json_decode($response->getContent(), false);

        $this->assertCount(
            $originalSectionCount + 1,
            $departmentJSON->sections
        );

        $this->assertArrayContainsSameKeyWithValue($departmentJSON->sections, 'id', $newSectionId);
        $this->assertListContainsSameObjectWithValue($departmentDB->getSections(), 'getId', $newSectionId);
    }

    /**
     * @test
     * @throws Exception
     */
    public function apiPutDepartmentWithManagers(): void
    {
        $departmentREF = $this->fixtures->getReference('department_' . random_int(0, 19));
        /* @var $departmentREF Department */
        $userREF = $this->fixtures->getReference('user_' . random_int(0, 19));
        /* @var $userREF User */
        $userId = $userREF->getId();

        $payload = <<<JSON
{
  "name": "department_test_put_with_manager",
  "shortName": "DM",
  "active": true,
  "managers": ["/api/users/{$userId}"]
}
JSON;

        $response = $this->getActionResponse(
            'PUT',
            '/api/departments/' . $departmentREF->getId(),
            $payload,
            [],
            200
        );
        $departmentJSON = json_decode($response->getContent(), false);

        $departmentDB = $this->entityManager->getRepository(Department::class)->find($departmentREF->getId());
        /* @var $departmentDB Department */

        $this->assertNotNull($departmentJSON);
        $this->assertEquals($departmentDB->getId(), $departmentJSON->id);
        $this->assertEquals($departmentDB->getName(), $departmentJSON->name);
        $this->assertEquals($departmentDB->getShortName(), $departmentJSON->shortName);
        $this->assertEquals($departmentDB->getActive(), $departmentJSON->active);

        $this->assertListContainsSameObjectWithValue($departmentDB->getManagers(), 'getId', $userId);
    }

    /**
     * @test
     * @throws Exception
     */
    public function apiPutDepartmentWithUsers(): void
    {
        $departmentREF = $this->fixtures->getReference('department_' . random_int(0, 19));
        /* @var $departmentREF Department */

        $user = $this->getEntityFromReference('user_' . random_int(0, 19));
        /* @var $user User */
        $user->setSection(null);
        $user->setDepartment(
            $this->getEntityFromReference('department_admin')
        );
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);

        $originalDepartmentCount = count($departmentREF->getUsers());

        $newUserArray[] = '"/api/users/' . $user->getId() . '"';
        foreach ($departmentREF->getUsers() as $departmentUser) {
            $newUserArray[] = sprintf('"/api/users/%s"', $departmentUser->getId());
        }
        $newUserList = implode(', ', $newUserArray);

        $payload = <<<JSON
{
  "name": "department_test_put_with_user",
  "shortName": "DPP",
  "active": true,
  "users": [$newUserList]
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_PUT,
            '/api/departments/' . $departmentREF->getId(),
            $payload,
            [],
            200
        );

        $this->assertJson($response->getContent());
        $departmentJSON = json_decode($response->getContent(), false);

        $departmentDB = $this->entityManager->getRepository(Department::class)->find($departmentREF->getId());
        /* @var $departmentDB Department */

        $this->assertNotNull($departmentJSON);
        $this->assertEquals($departmentDB->getId(), $departmentJSON->id);
        $this->assertEquals($departmentDB->getName(), $departmentJSON->name);
        $this->assertEquals($departmentDB->getShortName(), $departmentJSON->shortName);
        $this->assertEquals($departmentDB->getActive(), $departmentJSON->active);

        $this->assertCount($originalDepartmentCount + 1, $departmentDB->getUsers());
        $this->assertListContainsSameObjectWithValue($departmentDB->getUsers(), 'getId', $user->getId());
    }
}
