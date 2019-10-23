<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\DataFixtures\PresenceTypeFixtures;
use App\Entity\PresenceType;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use Exception;

class PresenceTypeTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetPresenceTypes(): void
    {
        $presenceTypesDB = $this->entityManager->getRepository(PresenceType::class)->findAll();
        /* @var $presenceTypesDB PresenceType */
        $response = $this->getActionResponse(self::HTTP_GET, '/api/presence_types');
        $presenceTypesJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($presenceTypesJSON);
        $this->assertEquals(count($presenceTypesDB), $presenceTypesJSON->{'hydra:totalItems'});
    }

    /**
     * @test
     * @dataProvider apiGetPresenceTypeProvider
     * @param string $referenceName
     * @throws NotFoundReferencedUserException
     */
    public function apiGetPresenceType($referenceName): void
    {
        $presenceTypeDB = $this->fixtures->getReference($referenceName);
        /* @var $presenceTypeDB PresenceType */

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/presence_types/' . $presenceTypeDB->getId()
        );
        $presenceTypeJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($presenceTypeJSON);
        $this->assertEquals($presenceTypeDB->getId(), $presenceTypeJSON->id);
        $this->assertEquals($presenceTypeDB->getShortName(), $presenceTypeJSON->shortName);
        $this->assertEquals($presenceTypeDB->getName(), $presenceTypeJSON->name);
        $this->assertEquals($presenceTypeDB->getActive(), $presenceTypeJSON->active);
        $this->assertEquals($presenceTypeDB->getCreateRestriction(), $presenceTypeJSON->createRestriction);
        $this->assertEquals($presenceTypeDB->getEditRestriction(), $presenceTypeJSON->editRestriction);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function apiGetPresenceTypeProvider(): array
    {
        $referenceList = [];

        for ($i = 0; $i < 10; $i++) {
            $randomPresenceTypeId = random_int(0, PresenceTypeFixtures::FIXTURES_RECORD_COUNT - 1);
            $referenceList[] = ['presence_type_' . $randomPresenceTypeId];
        }

        return $referenceList;
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiPostPresenceTypeShouldBeProhibited(): void
    {
        $payload = <<<JSON
{
  "shortName": "SN",
  "name": "presence type test",
  "active": true
}
JSON;

        $this->getActionResponse(
            self::HTTP_POST,
            '/api/presence_types',
            $payload,
            [],
            405
        );
    }

    /**
     * @test
     * @throws Exception
     */
    public function apiPutPresenceTypeShouldBeProhibited(): void
    {
        $presenceTypeREF = $this->fixtures->getReference(
            'presence_type_' . random_int(0, PresenceTypeFixtures::FIXTURES_RECORD_COUNT - 1)
        );
        /* @var $presenceTypeREF PresenceType */

        $payload = <<<'JSON'
{
  "shortName": "SN2",
  "name": "presence type test put",
  "active": false
}
JSON;

        $this->getActionResponse(
            self::HTTP_PUT,
            '/api/presence_types/' . $presenceTypeREF->getId(),
            $payload,
            [],
            405
        );
    }
}
