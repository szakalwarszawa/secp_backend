<?php

namespace App\Tests\EventSubscriber;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\UserWorkScheduleStatusFixtures;
use App\Entity\User;
use App\Entity\UserWorkSchedule;
use App\Entity\UserWorkScheduleDay;
use App\Entity\UserWorkScheduleStatus;
use App\Entity\WorkScheduleProfile;
use App\Tests\AbstractWebTestCase;
use Exception;

/**
 * Class UserWorkScheduleChangeStatusTest
 * @package App\Tests\EventSubscriber
 */
class UserWorkScheduleChangeStatusTest extends AbstractWebTestCase
{
    public function changeWorkScheduleStatusProvider(): array
    {
        return [
            [['2019-09-14', '2019-09-29'], ['2019-09-14', '2019-09-29'], []],
            [['2019-09-01', '2019-09-20'], ['2019-09-21', '2019-09-30'], []],
            [['2019-09-01', '2019-09-20'], ['2019-09-01', '2019-09-20'], []],
        ];
    }

    /**
     * @test
     * @dataProvider changeWorkScheduleStatusProvider
     * @throws Exception
     */
    public function changeWorkScheduleStatus($scheduleBase, $scheduleNew, $scheduleNew2): void
    {
        $userWorkScheduleToClean = [];

        $scheduleConfirmed = $this->makeUserWorkSchedule(
            $scheduleBase[0],
            $scheduleBase[1],
            $this->getEntityFromReference(UserFixtures::REF_USER_USER),
            $this->getEntityFromReference('work_schedule_profile_2'),
            $this->getEntityFromReference(UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT)
        );

        $scheduleConfirmedDb = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($scheduleConfirmed->getId());
        /* @var $scheduleConfirmedDb UserWorkSchedule */
        $userWorkScheduleToClean[] = $scheduleConfirmedDb;

        $scheduleNew = $this->makeUserWorkSchedule(
            $scheduleNew[0],
            $scheduleNew[1],
            $this->getEntityFromReference(UserFixtures::REF_USER_USER),
            $this->getEntityFromReference('work_schedule_profile_2'),
            $this->getEntityFromReference(UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT)
        );

        $scheduleNewDb = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($scheduleNew->getId());
        /* @var $scheduleNewDb UserWorkSchedule */
        $userWorkScheduleToClean[] = $scheduleNewDb;

        foreach ($scheduleConfirmedDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
            $this->assertTrue($userWorkScheduleDay->getVisibility());
        }

        foreach ($scheduleNewDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
            $this->assertFalse($userWorkScheduleDay->getVisibility());
        }

        $scheduleNewDb->setStatus($this->getEntityFromReference(UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT));

        $this->saveToDb($scheduleNewDb);

        foreach ($scheduleConfirmedDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
            self::$container->get('doctrine')
                ->getManager()
                ->refresh($userWorkScheduleDay);

            if (strtotime($userWorkScheduleDay->getDayDefinition()->getId()) <= time()) {
                $this->assertTrue(
                    $userWorkScheduleDay->getVisibility(),
                    sprintf(
                        '%s - %s - %s',
                        $userWorkScheduleDay->getId(),
                        $userWorkScheduleDay->getDayDefinition()->getId(),
                        $userWorkScheduleDay->getVisibility()
                    )
                );
            } else {
                $this->assertFalse(
                    $userWorkScheduleDay->getVisibility(),
                    sprintf(
                        '%s - %s - %s',
                        $userWorkScheduleDay->getId(),
                        $userWorkScheduleDay->getDayDefinition()->getId(),
                        $userWorkScheduleDay->getVisibility()
                    )
                );
            }
        }

        foreach ($scheduleNewDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
            self::$container->get('doctrine')
                ->getManager()
                ->refresh($userWorkScheduleDay);

            if (strtotime($userWorkScheduleDay->getDayDefinition()->getId()) <= time()) {
                $this->assertFalse($userWorkScheduleDay->getVisibility());
            } else {
                $this->assertTrue($userWorkScheduleDay->getVisibility());
            }
        }

        $this->cleanUserWorkSchedule($userWorkScheduleToClean);

        return;


        $previousUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array('userWorkSchedule' => $previousScheduleId, "visibility" => false));

        $previousScheduleAffectedDays = count($previousUpdated);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array('userWorkSchedule' => $currentScheduleId, 'visibility' => false));

        $currentScheduleAffectedDays = count($currentUpdated);

        $today = strtotime(date('Y-m-d'));
        $previousWorkScheduleEnd = strtotime($startTo);

        $daysBetween = ceil(abs($previousWorkScheduleEnd - $today) / 86400);
        $this->assertEquals($previousScheduleAffectedDays, $daysBetween);
        $this->assertEquals($currentScheduleAffectedDays, 0);

        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($previousScheduleId);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($currentScheduleId);

        self::$container->get('doctrine')
            ->getManager()
            ->remove($currentUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->remove($userWorkScheduleUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();
    }

    /**
     * @param string $startFrom
     * @param string $startTo
     * @param User $owner
     * @param WorkScheduleProfile $workScheduleProfile
     * @param UserWorkScheduleStatus $status
     * @return UserWorkSchedule
     * @throws Exception
     */
    private function makeUserWorkSchedule(
        string $startFrom,
        string $startTo,
        User $owner,
        WorkScheduleProfile $workScheduleProfile,
        UserWorkScheduleStatus $status
    ): UserWorkSchedule {
        $this->assertInstanceOf(User::class, $owner);
        $this->assertInstanceOf(WorkScheduleProfile::class, $workScheduleProfile);
        $this->assertInstanceOf(UserWorkScheduleStatus::class, $status);

        $userWorkSchedule = new UserWorkSchedule();
        $userWorkSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus($status)
            ->setFromDate(new \DateTime($startFrom))
            ->setToDate(new \DateTime($startTo));

        $this->saveToDb($userWorkSchedule);

        return $userWorkSchedule;
    }

    /**
     * @param $entity
     */
    private function saveToDb($entity): void
    {
        self::$container->get('doctrine')
            ->getManager()
            ->persist($entity);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();
    }

    /**
     * @param UserWorkSchedule[] $userWorkSchedules
     */
    private function cleanUserWorkSchedule(array $userWorkSchedules): void
    {
        foreach ($userWorkSchedules as $userWorkSchedule) {
            foreach ($userWorkSchedule->getUserWorkScheduleDays() as $userWorkScheduleDay) {
                self::$container->get('doctrine')
                    ->getManager()
                    ->remove($userWorkScheduleDay);
            }

            self::$container->get('doctrine')
                ->getManager()
                ->remove($userWorkSchedule);

            self::$container->get('doctrine')
                ->getManager()
                ->flush();
        }
    }

    /**
     * @test
     * @throws Exception
     *
     * 14 15 16 |17| 18 19
     *       16 |17| 18 19 20 21
     *
     */
    public function previousStartEarlienThanCurrentSchedule()
    {
        $startFrom = '2019-09-14';
        $startTo = '2019-09-29';
        $endFrom = '2020-09-16';
        $endTo = '2020-09-21';

        $owner = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        $this->assertInstanceOf(User::class, $owner);

        $workScheduleProfile = $this->getEntityFromReference('work_schedule_profile_2');
        $this->assertInstanceOf(WorkScheduleProfile::class, $workScheduleProfile);

        $previousSchedule = new UserWorkSchedule();
        $previousSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(UserWorkSchedule::STATUS_HR_ACCEPT)
            ->setFromDate(new \DateTime($startFrom))
            ->setToDate(new \DateTime($startTo));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($previousSchedule);

        $currentSchedule = new UserWorkSchedule();
        $currentSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(UserWorkSchedule::STATUS_OWNER_EDIT)
            ->setFromDate(new \DateTime($endFrom))
            ->setToDate(new \DateTime($endTo));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($currentSchedule);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $previousScheduleId = $previousSchedule->getId();
        $currentScheduleId = $currentSchedule->getId();

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($currentScheduleId);

        $currentUpdated->setStatus(UserWorkSchedule::STATUS_HR_ACCEPT);

        self::$container->get('doctrine')
            ->getManager()
            ->persist($currentUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $previousUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array('userWorkSchedule' => $previousScheduleId, "visibility" => false));

        $previousScheduleAffectedDays = count($previousUpdated);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array('userWorkSchedule' => $currentScheduleId, 'visibility' => false));

        $currentScheduleAffectedDays = count($currentUpdated);

        $today = strtotime(date('Y-m-d'));
        $previousWorkScheduleEnd = strtotime($startTo);

        $daysBetween = ceil(abs($previousWorkScheduleEnd - $today) / 86400);
        $this->assertEquals($previousScheduleAffectedDays, $daysBetween);
        $this->assertEquals($currentScheduleAffectedDays, 0);

        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($previousScheduleId);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($currentScheduleId);

        self::$container->get('doctrine')
            ->getManager()
            ->remove($currentUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->remove($userWorkScheduleUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();
    }

    /**
     * @test
     * @throws Exception
     *
     *      |17| 18 19 20 21 22
     *      |17| 18 19 20 21 22
     *
     */
    public function previousAndCurrentSchedulesStartsToday()
    {
        $startFrom = '2019-09-16';
        $startTo = '2019-09-28';
        $endFrom = '2020-09-16';
        $endTo = '2020-09-28';

        $owner = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        $this->assertInstanceOf(User::class, $owner);

        $workScheduleProfile = $this->getEntityFromReference('work_schedule_profile_2');
        $this->assertInstanceOf(WorkScheduleProfile::class, $workScheduleProfile);

        $previousSchedule = new UserWorkSchedule();
        $previousSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(UserWorkSchedule::STATUS_HR_ACCEPT)
            ->setFromDate(new \DateTime($startFrom))
            ->setToDate(new \DateTime($startTo));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($previousSchedule);

        $currentSchedule = new UserWorkSchedule();
        $currentSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(UserWorkSchedule::STATUS_OWNER_EDIT)
            ->setFromDate(new \DateTime($endFrom))
            ->setToDate(new \DateTime($endTo));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($currentSchedule);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $previousScheduleId = $previousSchedule->getId();
        $currentScheduleId = $currentSchedule->getId();

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($currentScheduleId);

        $currentUpdated->setStatus(UserWorkSchedule::STATUS_HR_ACCEPT);

        self::$container->get('doctrine')
            ->getManager()
            ->persist($currentUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $previousUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $previousScheduleId, "visibility" => false));

        $previousScheduleAffectedDays = count($previousUpdated);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $currentScheduleId, "visibility" => false));

        $currentScheduleAffectedDays = count($currentUpdated);

        $today = strtotime(date('Y-m-d'));
        $previousWorkscheduleEnd = strtotime($startTo);

        $daysBetween = ceil(abs($previousWorkscheduleEnd - $today) / 86400);
        $this->assertEquals($previousScheduleAffectedDays, $daysBetween);
        $this->assertEquals($currentScheduleAffectedDays, 0);

        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($previousScheduleId);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($currentScheduleId);

        self::$container->get('doctrine')
            ->getManager()
            ->remove($currentUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->remove($userWorkScheduleUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

    }

    /**
     * @test
     * @throws Exception
     *
     *       15 16 17 18 19 20 21
     * 09 10 11 12 13 14 15 16 17 18
     *
     */
    public function currentScheduleStartsEarlierThanPreviousScheudule()
    {
        $startFrom = '2019-09-15';
        $startTo = '2019-09-28';
        $endFrom = '2020-09-09';
        $endTo = '2020-09-19';

        $owner = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        $this->assertInstanceOf(User::class, $owner);

        $workScheduleProfile = $this->getEntityFromReference('work_schedule_profile_2');
        $this->assertInstanceOf(WorkScheduleProfile::class, $workScheduleProfile);

        $previousSchedule = new UserWorkSchedule();
        $previousSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(UserWorkSchedule::STATUS_HR_ACCEPT)
            ->setFromDate(new \DateTime($startFrom))
            ->setToDate(new \DateTime($startTo));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($previousSchedule);

        $currentSchedule = new UserWorkSchedule();
        $currentSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(UserWorkSchedule::STATUS_OWNER_EDIT)
            ->setFromDate(new \DateTime($endFrom))
            ->setToDate(new \DateTime($endTo));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($currentSchedule);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $previousScheduleId = $previousSchedule->getId();
        $currentScheduleId = $currentSchedule->getId();

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($currentScheduleId);

        $currentUpdated->setStatus(UserWorkSchedule::STATUS_HR_ACCEPT);

        self::$container->get('doctrine')
            ->getManager()
            ->persist($currentUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $previousUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $previousScheduleId, "visibility" => false));

        $previousScheduleAffectedDays = count($previousUpdated);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $currentScheduleId, "visibility" => false));

        $currentScheduleAffectedDays = count($currentUpdated);

        $today = strtotime(date('Y-m-d'));
        $previousWorkscheduleEnd = strtotime($startTo);

        $daysBetween = ceil(abs($previousWorkscheduleEnd - $today) / 86400);
        $this->assertEquals($previousScheduleAffectedDays, $daysBetween);
        $this->assertEquals($currentScheduleAffectedDays, 0);

        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($previousScheduleId);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($currentScheduleId);

        self::$container->get('doctrine')
            ->getManager()
            ->remove($currentUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->remove($userWorkScheduleUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();
    }

    /**
     * @test
     * @throws Exception
     *
     *       15 16 |17| 18 19 20 21
     *                        20 21 22 23 24 25 26
     *
     */
    public function currentScheduleFarAwayInTheFutureFromToday()
    {
        $startFrom = '2019-09-14';
        $startTo = '2019-09-29';
        $endFrom = '2020-09-25';
        $endTo = '2020-09-31';

        $owner = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        $this->assertInstanceOf(User::class, $owner);

        $workScheduleProfile = $this->getEntityFromReference('work_schedule_profile_2');
        $this->assertInstanceOf(WorkScheduleProfile::class, $workScheduleProfile);

        $previousSchedule = new UserWorkSchedule();
        $previousSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(UserWorkSchedule::STATUS_HR_ACCEPT)
            ->setFromDate(new \DateTime($startFrom))
            ->setToDate(new \DateTime($startTo));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($previousSchedule);

        $currentSchedule = new UserWorkSchedule();
        $currentSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(UserWorkSchedule::STATUS_OWNER_EDIT)
            ->setFromDate(new \DateTime($endFrom))
            ->setToDate(new \DateTime($endTo));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($currentSchedule);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $previousScheduleId = $previousSchedule->getId();
        $currentScheduleId = $currentSchedule->getId();

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($currentScheduleId);

        $currentUpdated->setStatus(UserWorkSchedule::STATUS_HR_ACCEPT);

        self::$container->get('doctrine')
            ->getManager()
            ->persist($currentUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $previousUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $previousScheduleId, "visibility" => false));

        $previousScheduleAffectedDays = count($previousUpdated);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $currentScheduleId, "visibility" => false));

        $currentScheduleAffectedDays = count($currentUpdated);

        $today = strtotime(date('Y-m-d'));
        $previousWorkscheduleEnd = strtotime($startTo);

        $daysBetween = ceil(abs($previousWorkscheduleEnd - $today) / 86400);
        $this->assertEquals($previousScheduleAffectedDays, $daysBetween);
        $this->assertEquals($currentScheduleAffectedDays, 0);

        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($previousScheduleId);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($currentScheduleId);

        self::$container->get('doctrine')
            ->getManager()
            ->remove($currentUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->remove($userWorkScheduleUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();
    }

    /**
     * @test
     * @throws Exception
     *
     * 01 02 03 04 05 06 07 08 09 10 11 .............|17|
     *                                   13 14 15 16 |17| 18 19 20 21 22 23
     *
     */
    public function previousScheduleEndsInThePastFarAwayFromToday()
    {
        $startFrom = '2019-09-01';
        $startTo = '2019-09-17';
        $endFrom = '2020-09-16';
        $endTo = '2020-09-31';

        $owner = $this->getEntityFromReference(UserFixtures::REF_USER_USER);
        $this->assertInstanceOf(User::class, $owner);

        $workScheduleProfile = $this->getEntityFromReference('work_schedule_profile_2');
        $this->assertInstanceOf(WorkScheduleProfile::class, $workScheduleProfile);

        $previousSchedule = new UserWorkSchedule();
        $previousSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(UserWorkSchedule::STATUS_HR_ACCEPT)
            ->setFromDate(new \DateTime($startFrom))
            ->setToDate(new \DateTime($startTo));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($previousSchedule);

        $currentSchedule = new UserWorkSchedule();
        $currentSchedule->setOwner($owner)
            ->setWorkScheduleProfile($workScheduleProfile)
            ->setStatus(UserWorkSchedule::STATUS_OWNER_EDIT)
            ->setFromDate(new \DateTime($endFrom))
            ->setToDate(new \DateTime($endTo));

        self::$container->get('doctrine')
            ->getManager()
            ->persist($currentSchedule);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $previousScheduleId = $previousSchedule->getId();
        $currentScheduleId = $currentSchedule->getId();

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($currentScheduleId);

        $currentUpdated->setStatus(UserWorkSchedule::STATUS_HR_ACCEPT);

        self::$container->get('doctrine')
            ->getManager()
            ->persist($currentUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();

        $previousUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $previousScheduleId, "visibility" => false));

        $previousScheduleAffectedDays = count($previousUpdated);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkScheduleDay::class)
            ->findBy(array("userWorkSchedule" => $currentScheduleId, "visibility" => false));

        $currentScheduleAffectedDays = count($currentUpdated);

        $today = strtotime(date('Y-m-d'));
        $previousWorkscheduleEnd = strtotime($startTo);

        $daysBetween = ceil(abs($previousWorkscheduleEnd - $today) / 86400);
        $this->assertEquals($previousScheduleAffectedDays, 0);
        $this->assertEquals($currentScheduleAffectedDays, 0);

        $userWorkScheduleUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($previousScheduleId);

        $currentUpdated = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($currentScheduleId);

        self::$container->get('doctrine')
            ->getManager()
            ->remove($currentUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->remove($userWorkScheduleUpdated);

        self::$container->get('doctrine')
            ->getManager()
            ->flush();
    }
}
