<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\DataFixtures\UserFixtures;
use App\Entity\AppIssue;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use App\Tests\UserUtil;

/**
 * Class AppIssueTest
 */
class AppIssueTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiPostAppIssue(): void
    {
        $payload = <<<'JSON'
{
  "description": "There is single problem with.",
  "subject": "Issue report",
  "reporterName": "Janusz Tracz"
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_POST,
            '/api/app_issues',
            $payload,
            [],
            201,
            UserFixtures::REF_USER_HR_MANAGER
        );

        $responseJsonData = json_decode($response->getContent(), false);
        $this->assertNotNull($responseJsonData->id);
        $this->assertGreaterThan(0, $responseJsonData->redmineTaskId);

        $this->assertNotNull($responseJsonData);
        $this->assertIsNumeric($responseJsonData->id);

        /**
         * reporterName is ignored because at this step user is logged.
         */
        $this->assertEquals(UserUtil::DEFAULT_USER, $responseJsonData->reporterName);

        /**
         * @var AppIssue $issueDB
         */
        $issueDB = $this
            ->entityManager
            ->getRepository(AppIssue::class)
            ->findOneById($responseJsonData->id)
        ;

        $this->assertNotNull($issueDB);
        $this->assertEquals($issueDB->getId(), $responseJsonData->id);
        $this->assertEquals($issueDB->getRedmineTaskId(), $responseJsonData->redmineTaskId);
    }
}
