<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\UserWorkScheduleStatus;
use App\Tests\AbstractWebTestCase;

/**
 * Class UserWorkScheduleStatusTest
 */
class UserWorkScheduleStatusTest extends AbstractWebTestCase
{
    /**
     * Test UserWorkScheduleStatus class.
     */
    public function testApiGetUserWorkScheduleStatuses(): void
    {
        $userWorkScheduleStatuses = $this
            ->entityManager
            ->getRepository(UserWorkScheduleStatus::class)
            ->findAll();

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_work_schedule_statuses',
            null,
            [],
            200,
            self::REF_MANAGER
        );

        $userWorkScheduleStatusesJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userWorkScheduleStatusesJSON);
        $this->assertEquals(count($userWorkScheduleStatuses), $userWorkScheduleStatusesJSON->{'hydra:totalItems'});
    }
}
