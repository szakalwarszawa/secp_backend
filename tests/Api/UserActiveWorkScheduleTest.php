<?php

namespace App\Tests\Api;

use App\DataFixtures\UserFixtures;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;

class UserActiveWorkScheduleTest extends AbstractWebTestCase
{
    /**
     * @test
     * @throws NotFoundReferencedUserException
     */
    public function apiGetUserWorkSchedulesOwnActive(): void
    {
        $userWorkScheduleDB = $this->entityManager->getRepository(UserWorkScheduleDay::class)
            ->findWorkDayBetweenDate(
                $this->fixtures->getReference(UserFixtures::REF_USER_ADMIN),
                '2019-07-01',
                '2019-07-10'
            );
        /* @var $userWorkScheduleDB UserWorkSchedule */
        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_work_schedule_days/own/active/2019-07-01/2019-07-10'
        );
        $this->assertJson($response->getContent());
        $userWorkScheduleJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userWorkScheduleJSON);
        $this->assertEquals(count($userWorkScheduleDB), $userWorkScheduleJSON->{'hydra:totalItems'});
    }
}
