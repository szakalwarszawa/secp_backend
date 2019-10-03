<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Tests\AbstractWebTestCase;
use Exception;

/**
 * Class ApplicationInfoTest
 */
class ApplicationInfoTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function apiGetApplicationInfo(): void
    {
        $response = $this->getActionResponse(
            'GET',
            '/api/application/info',
            null,
            [],
            200
        );

        $this->assertEquals($response->getStatusCode(), 200);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('git_commit', $data);
        $this->assertArrayHasKey('git_tag', $data);
        $this->assertArrayHasKey('deploy_time', $data);
    }
}