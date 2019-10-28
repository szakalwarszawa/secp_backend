<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\DataFixtures\UserFixtures;
use App\Entity\DayDefinition;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

/**
 * Class DayDefinitionTest
 */
class DayDefinitionTest extends AbstractWebTestCase
{
    private const DAY_DEFINITION_TEST_ID = '2000-01-11';

    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetDayDefinitions(): void
    {
        $dayDefinitionsDB = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(DayDefinition::class)
            ->findAll();
        /* @var $sectionDB DayDefinition[] */
        $response = $this->getActionResponse('GET', '/api/day_definitions');
        $dayDefinitionsJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($dayDefinitionsJSON);
        $this->assertEquals(count($dayDefinitionsDB), $dayDefinitionsJSON->{'hydra:totalItems'});
    }

    /**
     * @test
     * @dataProvider apiGetDayDefinitionProvider
     * @param string $referenceName
     * @throws NotFoundReferencedUserException
     */
    public function apiGetDayDefinition($referenceName): void
    {
        $dayDefinitionDB = $this->fixtures->getReference($referenceName);
        /* @var $dayDefinitionDB DayDefinition */

        $response = $this->getActionResponse('GET', '/api/day_definitions/' . $dayDefinitionDB->getId());
        $dayDefinitionJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($dayDefinitionJSON);
        $this->assertEquals($dayDefinitionDB->getId(), $dayDefinitionJSON->id);
        $this->assertEquals($dayDefinitionDB->getWorkingDay(), $dayDefinitionJSON->workingDay);
        $this->assertEquals($dayDefinitionDB->getNotice(), $dayDefinitionJSON->notice);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function apiGetDayDefinitionProvider(): array
    {
        $referenceList = [];

        for ($i = 0; $i < 10; $i++) {
            $randomSection = random_int(0, 19);
            $referenceList[] = ['day_definition_' . $randomSection];
        }

        return $referenceList;
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiPostDayDefinitionShouldBeProhibited(): void
    {
        $payload = <<<JSON
{
  "id": "2030-01-01",
  "workingDay": true,
  "notice": "Brak" 
}
JSON;

        $this->getActionResponse(
            self::HTTP_POST,
            '/api/day_definitions',
            $payload,
            [],
            405
        );
    }

    /**
     * @test
     * @throws Exception
     */
    public function apiPutDayDefinition(): void
    {
        $payload = <<<JSON
{
  "workingDay": false,
  "notice": "Nowy dzień niepracujący"
}
JSON;

        $beforeUpdateDayDefinition = self::$container
           ->get('doctrine')
            ->getManager()
            ->getRepository(DayDefinition::class)
            ->find(self::DAY_DEFINITION_TEST_ID)
        ;

        $response = $this->getActionResponse(
            self::HTTP_PUT,
            '/api/day_definitions/' . self::DAY_DEFINITION_TEST_ID,
            $payload,
            [],
            200,
            UserFixtures::REF_USER_ADMIN
        );
        $dayDefinitionJSON = json_decode($response->getContent(), false);

        $dayDefinitionDB = self::$container->get('doctrine')->getManager()->getRepository(DayDefinition::class)->find(
            self::DAY_DEFINITION_TEST_ID
        );

        /* @var $dayDefinitionDB DayDefinition */

        $this->assertNotNull($dayDefinitionJSON);
        $this->assertEquals($dayDefinitionDB->getId(), $dayDefinitionJSON->id);
        $this->assertEquals($dayDefinitionDB->getWorkingDay(), $dayDefinitionJSON->workingDay);
        $this->assertEquals($dayDefinitionDB->getNotice(), $dayDefinitionJSON->notice);

        $this->assertApiLogsSaving(
            sprintf(
                '/api/day_definitions/%s/logs',
                $dayDefinitionDB->getId()
            ),
            $beforeUpdateDayDefinition
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $dayDefinition = new DayDefinition();
        $dayDefinition->setId(self::DAY_DEFINITION_TEST_ID);
        $dayDefinition->setWorkingDay(true);
        $dayDefinition->setNotice(null);

        self::$container->get('doctrine')->getManager()->persist($dayDefinition);
        self::$container->get('doctrine')->getManager()->flush();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function tearDown(): void
    {
        $em = self::$container->get('doctrine')->getManager();
        /* @var $em EntityManager */

        $dayDefinition = $em->getRepository(DayDefinition::class)->find(self::DAY_DEFINITION_TEST_ID);
        /* @var $dayDefinition DayDefinition */

        if (!$dayDefinition) {
            return;
        }

        $em->remove($dayDefinition);

        $em->flush();

        parent::tearDown();
    }
}
