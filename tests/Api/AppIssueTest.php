<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\DataFixtures\UserFixtures;
use App\Entity\AppIssue;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;

/**
 * Class AppIssueTest
 */
class AppIssueTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiPostAppIssueAsLoggedUser(): void
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
         * Despite the fact that the parameter `reporterName` was passed,
         * it will not be used because the user has also sent a token
         * (6 parameter getActionResponse() `userReference`)
         */
        $userManager = $this->getEntityFromReference(UserFixtures::REF_USER_HR_MANAGER);
        $this->assertEquals($userManager->getUsername(), $responseJsonData->reporterName);

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
        $this->assertEquals($userManager->getUsername(), $issueDB->getReporterName());

        /**
         * Request as anonymous.
         * Route below is accessible to anonymous.
         */
        $response = $this->getActionResponse(
            self::HTTP_POST,
            '/api/app_issues',
            $payload,
            [],
            201,
            UserFixtures::REF_USER_HR_MANAGER,
            AbstractWebTestCase::CONTENT_TYPE_LD_JSON,
            true
        );

        $responseJsonData = json_decode($response->getContent(), false);

        /**
         * In that case there is no token provided in request (anonymousRequest parameter).
         * So request payload will be used completely (without ignoring the reporterName).
         */
        $this->assertEquals('Janusz Tracz', $responseJsonData->reporterName);
    }
}
