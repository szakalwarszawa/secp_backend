<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\DataFixtures\AbsenceTypeFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\AbsenceType;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use Exception;

class AbsenceTypeTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetAbsenceTypes(): void
    {
        $absenceTypesDB = $this->entityManager->getRepository(AbsenceType::class)->findAll();
        /* @var $absenceTypesDB AbsenceType */
        $response = $this->getActionResponse(self::HTTP_GET, '/api/absence_types');
        $absenceTypesJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($absenceTypesJSON);
        $this->assertEquals(count($absenceTypesDB), $absenceTypesJSON->{'hydra:totalItems'});
    }

    /**
     * @test
     * @dataProvider apiGetAbsenceTypeProvider
     * @param string $referenceName
     * @throws NotFoundReferencedUserException
     */
    public function apiGetAbsenceType($referenceName): void
    {
        $absenceTypeDB = $this->fixtures->getReference($referenceName);
        /* @var $absenceTypeDB AbsenceType */

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/absence_types/' . $absenceTypeDB->getId()
        );
        $absenceTypeJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($absenceTypeJSON);
        $this->assertEquals($absenceTypeDB->getId(), $absenceTypeJSON->id);
        $this->assertEquals($absenceTypeDB->getShortName(), $absenceTypeJSON->shortName);
        $this->assertEquals($absenceTypeDB->getName(), $absenceTypeJSON->name);
        $this->assertEquals($absenceTypeDB->getActive(), $absenceTypeJSON->active);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function apiGetAbsenceTypeProvider(): array
    {
        $referenceList = [];

        for ($i = 0; $i < 10; $i++) {
            $randomAbsenceTypeId = random_int(0, AbsenceTypeFixtures::FIXTURES_RECORD_COUNT - 1);
            $referenceList[] = ['absence_type_' . $randomAbsenceTypeId];
        }

        return $referenceList;
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiPostAbsenceTypeShouldBeProhibited(): void
    {
        $payload = <<<JSON
{
  "shortName": "SN", 
  "name": "absence type test",
  "active": true
}
JSON;

        $this->getActionResponse(
            self::HTTP_POST,
            '/api/absence_types',
            $payload,
            [],
            405
        );
    }

    /**
     * @test
     * @throws Exception
     */
    public function apiPutAbsenceTypeShouldBeProhibited(): void
    {
        $absenceTypeREF = $this->fixtures->getReference(
            'absence_type_' . random_int(0, AbsenceTypeFixtures::FIXTURES_RECORD_COUNT - 1)
        );
        /* @var $absenceTypeREF AbsenceType */

        $payload = <<<'JSON'
{
  "shortName": "SN2",
  "name": "absence type test put",
  "active": false
}
JSON;

        $this->getActionResponse(
            self::HTTP_PUT,
            '/api/absence_types/' . $absenceTypeREF->getId(),
            $payload,
            [],
            405
        );
    }
}
