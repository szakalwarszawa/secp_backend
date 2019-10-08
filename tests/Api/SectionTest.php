<?php


namespace App\Tests\Api;

use App\Entity\Department;
use App\Entity\Section;
use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use Exception;

class SectionTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetSections(): void
    {
        $sectionsDB = $this->entityManager->getRepository(Section::class)->findAll();
        /* @var $sectionDB Section */
        $sectionsDB = $this->entityManager->getRepository(Section::class)->findAll();
        $response = $this->getActionResponse('GET', '/api/sections');
        $sectionsJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($sectionsJSON);
        $this->assertEquals(count($sectionsDB), $sectionsJSON->{'hydra:totalItems'});
    }

    /**
     * @test
     * @dataProvider apiGetSectionProvider
     * @param string $referenceName
     * @throws NotFoundReferencedUserException
     */
    public function apiGetSection($referenceName): void
    {
        $sectionDB = $this->fixtures->getReference($referenceName);
        /* @var $sectionDB Section */

        $response = $this->getActionResponse('GET', '/api/sections/' . $sectionDB->getId());
        $sectionJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($sectionJSON);
        $this->assertEquals($sectionDB->getId(), $sectionJSON->id);
        $this->assertEquals($sectionDB->getName(), $sectionJSON->name);
        $this->assertEquals($sectionDB->getActive(), $sectionJSON->active);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function apiGetSectionProvider(): array
    {
        $referenceList = [];

        for ($i = 0; $i < 10; $i++) {
            $randomSection = random_int(0, 19);
            $referenceList[] = ['section_' . $randomSection];
        }

        return $referenceList;
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiPostSection(): void
    {
        $departmentId = $this->fixtures->getReference('department_0')->getId();

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

        $sectionJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($sectionJSON);
        $this->assertIsNumeric($sectionJSON->id);
        $this->assertEquals('section_test', $sectionJSON->name);
        $this->assertEquals(true, $sectionJSON->active);

        $response = $this->getActionResponse(
            'GET',
            '/api/sections/' . $sectionJSON->id
        );

        $sectionDB = $this->entityManager->getRepository(Section::class)->find($sectionJSON->id);
        /* @var $sectionDB Section */

        $sectionResponsJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($sectionResponsJSON);
        $this->assertEquals($sectionDB->getId(), $sectionResponsJSON->id);
        $this->assertEquals($sectionDB->getName(), $sectionResponsJSON->name);
        $this->assertEquals($sectionDB->getActive(), $sectionResponsJSON->active);
    }

    /**
     * @test
     * @throws Exception
     */
    public function apiPutSection(): void
    {
        $sectionREF = $this->fixtures->getReference('section_' . random_int(0, 19));
        /* @var $sectionREF Section */

        $payload = <<<'JSON'
{
  "name": "section_test_put",
  "active": false
}
JSON;

        $response = $this->getActionResponse(
            'PUT',
            '/api/sections/' . $sectionREF->getId(),
            $payload
        );
        $sectionJSON = json_decode($response->getContent(), false);

        $sectionDB = $this->entityManager->getRepository(Section::class)->find($sectionREF->getId());
        /* @var $sectionDB Section */

        $this->assertNotNull($sectionJSON);
        $this->assertEquals($sectionDB->getId(), $sectionJSON->id);
        $this->assertEquals($sectionDB->getName(), $sectionJSON->name);
        $this->assertEquals($sectionDB->getActive(), $sectionJSON->active);
    }

    /**
     * @test
     * @throws Exception
     */
    public function apiPutSectionWithManagers(): void
    {
        $sectionREF = $this->fixtures->getReference('section_' . random_int(0, 19));
        /* @var $sectionREF Section */
        $userREF = $this->fixtures->getReference('user_' . random_int(0, 19));
        /* @var $userREF User */
        $userId = $userREF->getId();

        $payload = <<<JSON
{
  "name": "section_test_put_with_manager",
  "active": true,
  "managers": ["/api/users/{$userId}"]
}
JSON;

        $response = $this->getActionResponse(
            'PUT',
            '/api/sections/' . $sectionREF->getId(),
            $payload
        );
        $sectionJSON = json_decode($response->getContent(), false);

        $sectionDB = $this->entityManager->getRepository(Section::class)->find($sectionREF->getId());
        /* @var $sectionDB Section */

        $this->assertNotNull($sectionJSON);
        $this->assertEquals($sectionDB->getId(), $sectionJSON->id);
        $this->assertEquals($sectionDB->getName(), $sectionJSON->name);
        $this->assertEquals($sectionDB->getActive(), $sectionJSON->active);

        $this->assertListContainsSameObjectWithValue($sectionDB->getManagers(), 'getId', $userId);
    }

    /**
     * @test
     * @throws Exception
     */
    public function apiPutSectionWithUsers(): void
    {
        $sectionREF = $this->fixtures->getReference('section_' . random_int(0, 19));
        /* @var $sectionREF Section */

        $user = $this->getEntityFromReference('user_' . random_int(0, 19));
        /* @var $user User */
        $user->setSection(null);
        $user->setDepartment(
            $this->entityManager
                ->getRepository(Department::class)
                ->find($sectionREF->getDepartment()->getId())
        );
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);

        $originalSectionCount = count($sectionREF->getUsers());

        $newUserArray[] = '"/api/users/' . $user->getId() . '"';
        foreach ($sectionREF->getUsers() as $sectionUser) {
            $newUserArray[] = sprintf('"/api/users/%s"', $sectionUser->getId());
        }
        $newUserList = implode(', ', $newUserArray);

        $payload = <<<JSON
{
  "name": "section_test_put_with_user",
  "active": true,
  "users": [$newUserList]
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_PUT,
            '/api/sections/' . $sectionREF->getId(),
            $payload
        );

        $this->assertJson($response->getContent());
        $sectionJSON = json_decode($response->getContent(), false);

        $sectionDB = $this->entityManager->getRepository(Section::class)->find($sectionREF->getId());
        /* @var $sectionDB Section */

        $this->assertNotNull($sectionJSON);
        $this->assertEquals($sectionDB->getId(), $sectionJSON->id);
        $this->assertEquals($sectionDB->getName(), $sectionJSON->name);
        $this->assertEquals($sectionDB->getActive(), $sectionJSON->active);

        $this->assertCount($originalSectionCount + 1, $sectionDB->getUsers());
        $this->assertListContainsSameObjectWithValue($sectionDB->getUsers(), 'getId', $user->getId());
    }

    /**
     * @test
     * @throws Exception
     */
    public function apiPutSectionWithUsersFromOtherDepartment(): void
    {
        $sectionREF = $this->fixtures->getReference('section_' . random_int(0, 19));
        /* @var $sectionREF Section */

        $user = $this->getEntityFromReference('user_' . random_int(0, 19));
        /* @var $user User */

        $user->setSection(null);
        $user->setDepartment(
            $this->getEntityFromReference('department_admin')
        );
        $this->entityManager->persist($user);
        $this->entityManager->flush($user);

        $originalSectionCount = count($sectionREF->getUsers());

        $newUserArray[] = '"/api/users/' . $user->getId() . '"';
        foreach ($sectionREF->getUsers() as $sectionUser) {
            $newUserArray[] = sprintf('"/api/users/%s"', $sectionUser->getId());
        }
        $newUserList = implode(', ', $newUserArray);

        $payload = <<<JSON
{
  "name": "section_test_put_with_user",
  "active": true,
  "users": [$newUserList]
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_PUT,
            '/api/sections/' . $sectionREF->getId(),
            $payload,
            [],
            400
        );

        $this->assertJson($response->getContent());
        $sectionJSON = json_decode($response->getContent(), false);

        $sectionDB = $this->entityManager->getRepository(Section::class)->find($sectionREF->getId());
        /* @var $sectionDB Section */

        $this->assertNotNull($sectionJSON);
        $this->assertEquals('Given Section not belong to user Department.', $sectionJSON->{'hydra:description'});

        $this->assertCount($originalSectionCount, $sectionDB->getUsers());
    }
}
