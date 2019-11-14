<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Tests\AbstractWebTestCase;
use Exception;

/**
 * Class ApplicationInfoTest
 */
class AppConfigTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function apiGetApplicationInfo(): void
    {
        $response = $this->getActionResponse(
            'GET',
            '/api/app_config',
            null,
            [],
            200
        );

        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertNotFalse($responseData);
        $this->assertArrayHasKey('hydra:member', $responseData);
        $this->assertArrayHasKey('hydra:totalItems', $responseData);
        $this->assertEquals(2, $responseData['hydra:totalItems']);
        $this->assertArrayContainsSameKeyWithValue(
            $responseData['hydra:member'],
            'configKey',
            'absenceToBeCompletedId'
        );
        $this->assertArrayContainsSameKeyWithValue(
            $responseData['hydra:member'],
            'configKey',
            'presenceAbsenceId'
        );
    }
}
