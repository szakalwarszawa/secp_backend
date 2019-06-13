<?php


namespace App\Tests\Api;

use App\Entity\WorkScheduleProfile;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use Exception;

class WorkScheduleProfileTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetWorkScheduleProfiles(): void
    {
        $workScheduleProfilesDB = $this->entityManager->getRepository(WorkScheduleProfile::class)->findAll();
        /* @var $workScheduleProfilesDB WorkScheduleProfile */
        $response = $this->getActionResponse(self::HTTP_GET, '/api/work_schedule_profiles');
        $workScheduleProfilesJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($workScheduleProfilesJSON);
        $this->assertEquals(count($workScheduleProfilesDB), $workScheduleProfilesJSON->{'hydra:totalItems'});
    }

    /**
     * @test
     * @dataProvider apiGetWorkScheduleProfileProvider
     * @param string $referenceName
     * @throws NotFoundReferencedUserException
     */
    public function apiGetWorkScheduleProfile($referenceName): void
    {
        $workScheduleProfileDB = $this->fixtures->getReference($referenceName);
        /* @var $workScheduleProfileDB WorkScheduleProfile */

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/work_schedule_profiles/' . $workScheduleProfileDB->getId()
        );
        $workScheduleProfileJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($workScheduleProfileJSON);
        $this->assertEquals($workScheduleProfileDB->getId(), $workScheduleProfileJSON->id);
        $this->assertEquals($workScheduleProfileDB->getName(), $workScheduleProfileJSON->name);
        $this->assertEquals($workScheduleProfileDB->getNotice(), $workScheduleProfileJSON->notice);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function apiGetWorkScheduleProfileProvider(): array
    {
        $referenceList = [];

        for ($i = 0; $i < 10; $i++) {
            $randomSection = random_int(0, 4);
            $referenceList[] = ['work_schedule_profile_' . $randomSection];
        }

        return $referenceList;
    }

    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiPostWorkScheduleProfileShouldBeProhibited(): void
    {
        $payload = <<<JSON
{
  "name": "work_schedule_profile_test",
  "notice": "Test work Schedule Profile Notice" 
}
JSON;

        $this->getActionResponse(
            self::HTTP_POST,
            '/api/work_schedule_profiles',
            $payload,
            [],
            405,
            self::REF_ADMIN
        );
    }

    /**
     * @test
     * @throws Exception
     */
    public function apiPutWorkScheduleProfileShouldBeProhibited(): void
    {
        $workScheduleProfileREF = $this->fixtures->getReference('work_schedule_profile_' . random_int(0, 4));
        /* @var $workScheduleProfileREF WorkScheduleProfile */

        $payload = <<<'JSON'
{
  "name": "section_test_put",
  "notice": "notice section test put"
}
JSON;

        $this->getActionResponse(
            self::HTTP_PUT,
            '/api/work_schedule_profiles/' . $workScheduleProfileREF->getId(),
            $payload,
            [],
            405,
            self::REF_ADMIN
        );
    }
}
