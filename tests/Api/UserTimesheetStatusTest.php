<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\UserTimesheetStatus;
use App\Tests\AbstractWebTestCase;

/**
 * Class UserTimesheetStatusTest
 */
class UserTimesheetStatusTest extends AbstractWebTestCase
{
    /**
     * Test UserTimesheetStatus class.
     */
    public function testApiGetUserTimesheetStatuses(): void
    {
        $userTimesheetStatuses = $this
            ->entityManager
            ->getRepository(UserTimesheetStatus::class)
            ->findAll();

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_timesheet_statuses',
            null,
            [],
            200,
            self::REF_MANAGER
        );

        $userTimesheetStatusesJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userTimesheetStatusesJSON);
        $this->assertEquals(count($userTimesheetStatuses), $userTimesheetStatusesJSON->{'hydra:totalItems'});
    }
}
