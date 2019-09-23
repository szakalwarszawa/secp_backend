<?php

namespace App\Tests\Api;

use App\Tests\AbstractWebTestCase;
use Exception;

/**
 * Class ApplicationInfoTest
 * @package App\Tests\Api
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
        $this->assertIsFloat((float)$response->getContent());
    }
}