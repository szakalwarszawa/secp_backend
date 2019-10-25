<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\UserTimesheetFixtures;
use App\DataFixtures\UserWorkScheduleFixtures;
use App\Entity\AbsenceType;
use App\Entity\PresenceType;
use App\Entity\UserTimesheet;
use App\Entity\UserTimesheetDay;
use App\Entity\UserWorkScheduleDay;
use App\Tests\AbstractWebTestCase;
use App\Tests\NotFoundReferencedUserException;
use Doctrine\ORM\NonUniqueResultException;
use Exception;

/**
 * Class UserTimesheetDayTest
 */
class UserTimesheetDayTest extends AbstractWebTestCase
{
    /**
     * @test
     *
     * @return void
     *
     * @throws NotFoundReferencedUserException
     */
    public function apiGetUserTimesheetDayDay(): void
    {
        $userTimesheetDaysDB = $this->entityManager
            ->getRepository(UserTimesheetDay::class)
            ->createQueryBuilder('p')
            ->innerJoin('p.userTimesheet', 'userTimesheet')
            ->andWhere('userTimesheet.owner = :owner')
            ->setParameter('owner', $this->fixtures->getReference(UserFixtures::REF_USER_USER))
            ->getQuery()
            ->getResult();
        /* @var $userTimesheetDaysDB UserTimesheetDay[] */

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_timesheet_days',
            null,
            [],
            200,
            UserFixtures::REF_USER_USER
        );
        $this->assertJson($response->getContent());
        $userTimesheetDaysJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userTimesheetDaysJSON);
        $this->assertEquals(count($userTimesheetDaysDB), $userTimesheetDaysJSON->{'hydra:totalItems'});
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws NotFoundReferencedUserException
     * @throws NonUniqueResultException
     */
    public function apiPostUserTimesheetDay(): void
    {
        $userTimesheetRef = $this->fixtures->getReference(UserTimesheetFixtures::REF_USER_TIMESHEET_USER_HR);
        /* @var $userTimesheetRef UserTimesheetDay */

        $presenceTypeRef = $this->fixtures->getReference('presence_type_4');
        /* @var $presenceTypeRef PresenceType */

        $absenceTypeRef = $this->fixtures->getReference('absence_type_7');
        /* @var $absenceTypeRef AbsenceType */

        $userWorkScheduleDayDB = $this->entityManager
            ->getRepository(UserWorkScheduleDay::class)
            ->findDayForUserWorkSchedule(
                $this->fixtures->getReference(
                    UserWorkScheduleFixtures::REF_FIXED_USER_WORK_SCHEDULE_USER_HR
                ),
                '2019-05-20'
            );
        /* @var $userWorkScheduleDayDB UserWorkScheduleDay */

        $this->assertInstanceOf(UserWorkScheduleDay::class, $userWorkScheduleDayDB);

        $payload = <<<JSON
{
    "userTimesheet": "/api/user_timesheets/{$userTimesheetRef->getId()}",
    "userWorkScheduleDay": "/api/user_work_schedule_days/{$userWorkScheduleDayDB->getId()}",
    "dayStartTime": "09:00",
    "dayEndTime": "17:00",
    "workingTime": "8.00",
    "presenceType": "/api/presence_types/{$presenceTypeRef->getId()}",
    "absenceType": "/api/absence_types/{$absenceTypeRef->getId()}"
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_POST,
            '/api/user_timesheet_days',
            $payload,
            [],
            201,
            UserFixtures::REF_USER_USER
        );

        $this->assertJson($response->getContent());
        $userTimesheetDayJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userTimesheetDayJSON);
        $this->assertIsNumeric($userTimesheetDayJSON->id);
        $this->assertEquals($userTimesheetRef->getId(), $userTimesheetDayJSON->userTimesheet->id);
        $this->assertEquals($userWorkScheduleDayDB->getId(), $userTimesheetDayJSON->userWorkScheduleDay->id);
        $this->assertEquals('09:00', $userTimesheetDayJSON->dayStartTime);
        $this->assertEquals('17:00', $userTimesheetDayJSON->dayEndTime);
        $this->assertEquals(8.00, $userTimesheetDayJSON->workingTime);
        $this->assertEquals($presenceTypeRef->getId(), $userTimesheetDayJSON->presenceType->id);
        $this->assertEquals($absenceTypeRef->getId(), $userTimesheetDayJSON->absenceType->id);

        $response = $this->getActionResponse(
            self::HTTP_GET,
            '/api/user_timesheet_days/' . $userTimesheetDayJSON->id,
            null,
            [],
            200,
            UserFixtures::REF_USER_USER
        );
        $this->assertJson($response->getContent());

        $userTimesheetDayDB = $this->entityManager
            ->getRepository(UserTimesheetDay::class)
            ->createQueryBuilder('p')
            ->innerJoin('p.userTimesheet', 'userTimesheet')
            ->andWhere('userTimesheet.owner = :owner')
            ->setParameter('owner', $this->fixtures->getReference(UserFixtures::REF_USER_USER))
            ->andWhere('p.id = :id')
            ->setParameter('id', $userTimesheetDayJSON->id)
            ->getQuery()
            ->getOneOrNullResult();
        /* @var $userTimesheetDayDB UserTimesheetDay */

        $userTimesheetDayJSON = json_decode($response->getContent(), false);

        $this->assertNotNull($userTimesheetDayJSON);
        $this->assertEquals($userTimesheetDayDB->getUserTimesheet()->getId(), $userTimesheetDayJSON->userTimesheet->id);
        $this->assertEquals(
            $userTimesheetDayDB->getUserWorkScheduleDay()->getId(),
            $userTimesheetDayJSON->userWorkScheduleDay->id
        );
        $this->assertEquals($userTimesheetDayDB->getDayStartTime(), $userTimesheetDayJSON->dayStartTime);
        $this->assertEquals($userTimesheetDayDB->getDayEndTime(), $userTimesheetDayJSON->dayEndTime);
        $this->assertEquals($userTimesheetDayDB->getWorkingTime(), $userTimesheetDayJSON->workingTime);
        $this->assertEquals($userTimesheetDayDB->getPresenceType()->getId(), $userTimesheetDayJSON->presenceType->id);
        $this->assertEquals($userTimesheetDayDB->getAbsenceType()->getId(), $userTimesheetDayJSON->absenceType->id);
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws NotFoundReferencedUserException
     * @throws Exception
     */
    public function apiPutUserTimesheetDay(): void
    {
        $userTimesheetREF = $this->fixtures->getReference(UserTimesheetFixtures::REF_USER_TIMESHEET_USER_EDIT);
        /* @var $userTimesheetREF UserTimesheet */

        $presenceTypeRef = $this->fixtures->getReference('presence_type_6');
        /* @var $presenceTypeRef PresenceType */

        $payload = <<<JSON
{
    "dayStartTime": "08:30",
    "dayEndTime": "17:30",
    "workingTime": "9.00",
    "presenceType": "/api/presence_types/{$presenceTypeRef->getId()}",
    "absenceType": null
}
JSON;

        $response = $this->getActionResponse(
            self::HTTP_PUT,
            '/api/user_timesheet_days/' . $userTimesheetREF->getUserTimesheetDays()[0]->getId(),
            $payload,
            [],
            200,
            UserFixtures::REF_USER_USER
        );

        $this->assertJson($response->getContent());
        $userTimesheetDayJSON = json_decode($response->getContent(), false);
        $this->assertNotNull($userTimesheetDayJSON);

        $this->assertIsNumeric($userTimesheetDayJSON->id);
        $this->assertEquals('08:30', $userTimesheetDayJSON->dayStartTime);
        $this->assertEquals('17:30', $userTimesheetDayJSON->dayEndTime);
        $this->assertEquals(9.00, $userTimesheetDayJSON->workingTime);
        $this->assertEquals($presenceTypeRef->getId(), $userTimesheetDayJSON->presenceType->id);
        $this->assertEquals(null, $userTimesheetDayJSON->absenceType);

        $userTimesheetDayDB = $this->entityManager->getRepository(UserTimesheetDay::class)->find(
            $userTimesheetREF->getUserTimesheetDays()[0]->getId()
        );
        /* @var $userTimesheetDayDB UserTimesheetDay */

        $this->assertEquals($userTimesheetDayDB->getUserTimesheet()->getId(), $userTimesheetDayJSON->userTimesheet->id);
        $this->assertEquals(
            $userTimesheetDayDB->getUserWorkScheduleDay()->getId(),
            $userTimesheetDayJSON->userWorkScheduleDay->id
        );
        $this->assertEquals($userTimesheetDayDB->getDayStartTime(), $userTimesheetDayJSON->dayStartTime);
        $this->assertEquals($userTimesheetDayDB->getDayEndTime(), $userTimesheetDayJSON->dayEndTime);
        $this->assertEquals($userTimesheetDayDB->getWorkingTime(), $userTimesheetDayJSON->workingTime);
        $this->assertEquals($userTimesheetDayDB->getPresenceType()->getId(), $userTimesheetDayJSON->presenceType->id);
        $this->assertEquals($userTimesheetDayDB->getAbsenceType(), $userTimesheetDayJSON->absenceType);
    }
}
