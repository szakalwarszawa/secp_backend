<?php

namespace App\Tests\Api;

use App\DataFixtures\UserFixtures;
use App\Entity\UserTimesheetDay;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;

class UserOwnTimesheetDayTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetUserWorkSchedulesOwnActive(): void
    {
        $userTimesheetDaysDB = $this->entityManager->getRepository(UserTimesheetDay::class)
            ->findWorkDayBetweenDate(
                $this->fixtures->getReference(UserFixtures::REF_USER_ADMIN),
                '2019-06-01',
                '2019-06-10'
            );
        /* @var $userTimesheetDaysDB UserTimesheetDay */
        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_timesheet_days/own/2019-06-01/2019-06-10'
        );
        $this->assertJson($response->getContent());
        $userWorkScheduleJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userWorkScheduleJSON);
        $this->assertEquals(count($userTimesheetDaysDB), $userWorkScheduleJSON->{'hydra:totalItems'});
    }
}
