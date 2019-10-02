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


class TestCase {

    /**
     * @var
     */
    private $name;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @var
     */
    private $start;
    /**
     * @var
     */
    private $end;
    /**
     * @var
     */
    private $user;
    /**
     * @var
     */
    private $workSchedule;
    /**
     * @var
     */
    private $baseStatus;
    /**
     * @var
     */
    private $endStatus;
    /**
     * @var array
     */
    private $days;

    private $preformattedDays;

    /**
     * @return mixed
     */
    public function getPreformattedDays()
    {
        return $this->preformattedDays;
    }

    /**
     * @param mixed $preformattedDays
     */
    public function setPreformattedDays($preformattedDays): void
    {
        $this->preformattedDays = $preformattedDays;
    }
    /**
     * TestCase constructor.
     * @param $start
     * @param $end
     * @param $user
     * @param $workSchedule
     * @param $baseStatus
     * @param $endStatus
     * @param array $days
     */
    public function __construct(string $name, string $start, string $end, $user, $workSchedule, $baseStatus, $endStatus, array $days)
    {
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->user = $user;
        $this->workSchedule = $workSchedule;
        $this->baseStatus = $baseStatus;
        $this->endStatus = $endStatus;
        $this->days = $days;
    }

    /**
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param mixed $start
     */
    public function setStart($start): void
    {
        $this->start = $start;
    }

    /**
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param mixed $end
     */
    public function setEnd($end): void
    {
        $this->end = $end;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getWorkSchedule()
    {
        return $this->workSchedule;
    }

    /**
     * @param mixed $workSchedule
     */
    public function setWorkSchedule($workSchedule): void
    {
        $this->workSchedule = $workSchedule;
    }

    /**
     * @return mixed
     */
    public function getBaseStatus()
    {
        return $this->baseStatus;
    }

    /**
     * @param mixed $baseStatus
     */
    public function setBaseStatus($baseStatus): void
    {
        $this->baseStatus = $baseStatus;
    }

    /**
     * @return mixed
     */
    public function getEndStatus()
    {
        return $this->endStatus;
    }

    /**
     * @param mixed $endStatus
     */
    public function setEndStatus($endStatus): void
    {
        $this->endStatus = $endStatus;
    }

    /**
     * @return array
     */
    public function getDays(): array
    {
        return $this->days;
    }

    /**
     * @param array $days
     */
    public function setDays(array $days): void
    {
        $this->days = $days;
    }
}

/**
 * Class UserWorkScheduleChangeStatusTest
 * @package App\Tests\EventSubscriber
 */
class UserWorkScheduleChangeStatusTest extends AbstractWebTestCase
{
    /**
     * @return array
     */
    public function changeWorkScheduleStatusProvider(): array
    {
        //23 24 25 26 |27| 28
//              25 26 |27| 28 29 30 01 02 03
        $testCases[] = [
            [
                   new TestCase(
                         'first is starting earlier, first schedule',
                         date('Y-m-d', strtotime('now -4 days')),
                         date('Y-m-d', strtotime('now +1 days')),
                        UserFixtures::REF_USER_USER,
                         'work_schedule_profile_2',
                        UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                        null,
                         array(
                            '-4 days' => true,
                            '-3 days' => true,
                            '-2 days' => true,
                            '-1 days' => true,
                            '+0 days' => true,
                            '+1 days' => false
                         )
                    ),
                    new TestCase(
                        'first is starting earlier, second schedule',
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                        array(
                            '-2 days' => false,
                            '-1 days' => false,
                            '+0 days' => false,
                            '+1 days' => true,
                            '+2 days' => true,
                            '+3 days' => true,
                            '+4 days' => true,
                            '+5 days' => true,
                            '+6 days' => true,
                        )
                    )
            ]
        ];
      //25 26 |27| 28 29 30 01 02 03
//23 24 25 26 |27| 28
        $testCases[] = [
            [
                new TestCase(
                    'second is starting earlier, first schedule',
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    array(
                        '-2 days' => true,
                        '-1 days' => true,
                        '+0 days' => true,
                        '+1 days' => false,
                        '+2 days' => true,
                        '+3 days' => true,
                        '+4 days' => true,
                        '+5 days' => true,
                        '+6 days' => true,
                    )
                ),
                new TestCase(
                    'second is starting earlier, second schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    array(
                        '-4 days' => false,
                        '-3 days' => false,
                        '-2 days' => false,
                        '-1 days' => false,
                        '+0 days' => false,
                        '+1 days' => true,
                    )
                )
            ]
        ];
//
//        //23 24 25 26 |27| 28
//        //23 24 25 26 |27| 28
        $testCases[] = [
            [
                 new TestCase(
                     'both are equal, first schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                     array(
                        '-4 days' => true,
                        '-3 days' => true,
                        '-2 days' => true,
                        '-1 days' => true,
                        '+0 days' => true,
                        '+1 days' => false,
                     )
                ),
                new TestCase(
                    'both are equal, second schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    array(
                        '-4 days' => false,
                        '-3 days' => false,
                        '-2 days' => false,
                        '-1 days' => false,
                        '+0 days' => false,
                        '+1 days' => true,
                    )
                )
            ]
        ];

//         25 26 |27| 28 29 30 01 02 03
 //23 24 25 26  |27| 28
   //                         01  02 03
        $testCases[] = [
            [
                new TestCase(
                    'third only future, first schedule',
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    array(
                        '-2 days' => true,
                        '-1 days' => true,
                        '+0 days' => true,
                        '+1 days' => false,
                        '+2 days' => true,
                        '+3 days' => true,
                        '+4 days' => false,
                        '+5 days' => false,
                        '+6 days' => false,
                    )
                ),

                new TestCase(
                    'third only future, second schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    array(
                        '-4 days' => false,
                        '-3 days' => false,
                        '-2 days' => false,
                        '-1 days' => false,
                        '+0 days' => false,
                        '+1 days' => true,
                    )
                ),

                new TestCase(
                    'third only future, third schedule',
                    date('Y-m-d', strtotime('now +4 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    array(
                        '+4 days' => true,
                        '+5 days' => true,
                        '+6 days' => true,
                    )
                )
            ]
        ];
////                      29 30 01 02 03
////23 24 25      |27|
        $testCases[] = [
            [
                new TestCase(
                    'gaps on both sides, first schedule',
                    date('Y-m-d', strtotime('now +2 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    array(
                        '+2 days' => true,
                        '+3 days' => true,
                        '+4 days' => true,
                        '+5 days' => true,
                        '+6 days' => true,
                    )
                ),
                new TestCase(
                    'gaps on both sides, second schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now -2 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    array(
                        '-2 days' => false,
                        '-3 days' => false,
                        '-4 days' => false,
                    )
                ),
            ]
        ];
//
//        //23 24 25 26 |27|
//                            //28 29 30
//              //25 26
        $testCases [] = [
            [
                new TestCase(
                    'three at once, first schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +0 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    array(
                        '-4 days' => true,
                        '-3 days' => true,
                        '-2 days' => true,
                        '-1 days' => true,
                        '-0 days' => true,
                    )
                ),
                new TestCase(
                    'three at once, second schedule',
                    date('Y-m-d', strtotime('now +0 days')),
                    date('Y-m-d', strtotime('now +3 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    array(
                        '+1 days' => true,
                        '+2 days' => true,
                        '+3 days' => true,
                    )
                ),
                new TestCase(
                    'three at once, third schedule',
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now -1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    array(
                        '-2 days' => false,
                        '-1 days' => false,
                    )
                )
            ]
        ];

        //one user does not affect other user
        //23 24 25 26 |27| 28
        //23 24 25 26 |27| 28
        $testCases[] = [
            [
                new TestCase(
                    'one does not affect each others, first schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_1',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    array(
                        '-4 days' => true,
                        '-3 days' => true,
                        '-2 days' => true,
                        '-1 days' => true,
                        '+0 days' => true,
                        '+1 days' => false,
                    )
                ),
                new TestCase(
                    'one does not affect each others, second schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_ADMIN,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    array(
                        '-4 days' => false,
                        '-3 days' => false,
                        '-2 days' => false,
                        '-1 days' => false,
                        '+0 days' => false,
                        '+1 days' => true,
                    )
                )
            ]
        ];

        return $testCases;
    }

    /**
     * @test
     * @param array $currentCase
     * @dataProvider changeWorkScheduleStatusProvider
     * @throws Exception
     */
    public function changeWorkScheduleStatus(array $currentCase): void
    {
        $userWorScheduleInTesting = [];
        // faza I - zakładanie dziewiczych harmonogramów oraz budowa odpowiednich dat ze stringow

        foreach ($currentCase as $currentCasee) {
            $daysToFormat = $currentCasee->getDays();
            $currentCasee->setPreformattedDays($daysToFormat);
            $builtDates = array();
            foreach ($daysToFormat as $day => $value) {
                $dateString = 'now ' . $day;
                $key = date('Y-m-d', strtotime($dateString));
                $val = $value;
                $builtDates[$key] = $val;
            }
            $currentCasee->setDays($builtDates);
        }

        foreach ($currentCase as $userScheduleCase) {
            $schedule = $this->makeUserWorkSchedule(
                $userScheduleCase->getStart(),
                $userScheduleCase->getEnd(),
                $this->getEntityFromReference($userScheduleCase->getUser()),
                $this->getEntityFromReference($userScheduleCase->getWorkSchedule()),
                $this->getEntityFromReference($userScheduleCase->getBaseStatus())
            );
            $userWorScheduleInTesting[$schedule->getId()] = [
                'schedule' => $schedule,
                'statusOriginRefName' => $userScheduleCase->getBaseStatus(),
                'statusFinalRefName' => $userScheduleCase->getEndStatus(),
                'expectedVisibilities' => $userScheduleCase->getDays(),
                'preFormatted' => $userScheduleCase->getPreformattedDays(),
                'name' => $userScheduleCase->getName()
            ];
        }

        // faza II - sprawdzanie czy dni harmonogramów maja poprawnie ustawioną flagę "visibility"
        foreach ($userWorScheduleInTesting as $userScheduleCase) {
            $scheduleDb = self::$container->get('doctrine')
                ->getManager()
                ->getRepository(UserWorkSchedule::class)
                ->find($userScheduleCase['schedule']->getId());
            /* @var $scheduleDb UserWorkSchedule */

            if ($userScheduleCase['statusOriginRefName'] === UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT) {
                foreach ($scheduleDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
                    $this->assertTrue($userWorkScheduleDay->getVisibility());
                }
            } else {
                foreach ($scheduleDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
                    $this->assertFalse($userWorkScheduleDay->getVisibility());
                }
            }
        }

        // faza III - zmiany statusu harmonogramów
        foreach ($userWorScheduleInTesting as $userScheduleCase) {
            $scheduleDb = self::$container->get('doctrine')
                ->getManager()
                ->getRepository(UserWorkSchedule::class)
                ->find($userScheduleCase['schedule']->getId());
            /* @var $scheduleDb UserWorkSchedule */

            if ($userScheduleCase['statusFinalRefName'] !== null) {
                $scheduleDb->setStatus($this->getEntityFromReference(UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT));
                $this->saveToDb($scheduleDb);
            }
        }

        // faza IV - badanie dni po zmianie statusów
        foreach ($userWorScheduleInTesting as $userScheduleCase) {
            //czy dzien w ogole wystepuje, zbadac czy true albo false
            $scheduleDb = self::$container->get('doctrine')
                ->getManager()
                ->getRepository(UserWorkSchedule::class)
                ->find($userScheduleCase['schedule']->getId());
            /* @var $scheduleDb UserWorkSchedule */

            $badCaseCounter = 0;

            foreach ($userScheduleCase['expectedVisibilities'] as $scheduleDayId => $expectedVisibility) {

                $scheduleDb1 = self::$container->get('doctrine')
                    ->getManager()
                    ->getRepository(UserWorkScheduleDay::class)
                    ->findBy(array('dayDefinition' =>  $scheduleDayId, 'userWorkSchedule' => $userScheduleCase['schedule']));

                $keys = array_keys($userScheduleCase['preFormatted']);

                $this->assertCount(1, $scheduleDb1,
                    sprintf(
                        "schedule: %s\nday: %s\ncase: %s",
                        $userScheduleCase['schedule']->getId(),
                        $scheduleDayId,
                        $keys[$badCaseCounter]
                    )
                );

                self::$container->get('doctrine')
                    ->getManager()
                    ->refresh($scheduleDb1[0]);

                $this->assertEquals(
                    $expectedVisibility,
                    $scheduleDb1[0]->getVisibility(),
                    sprintf(
                        "schedule: %s\nday: %s\ncase: %s \nlabel: %s",
                        $userScheduleCase['schedule']->getId(),
                        $scheduleDayId,
                        $keys[$badCaseCounter],
                        $userScheduleCase['name']
                    )
                );
                $badCaseCounter++;
            }
        }

        // faza V - czyszczenie po sobie tabel
        $this->cleanUserWorkSchedule($userWorScheduleInTesting);
        return;
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

        $this->assertInstanceOf(UserWorkSchedule::class, $userWorkSchedule);

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
            foreach ($userWorkSchedule['schedule']->getUserWorkScheduleDays() as $userWorkScheduleDay) {
                self::$container->get('doctrine')
                    ->getManager()
                    ->remove($userWorkScheduleDay);
            }

            self::$container->get('doctrine')
                ->getManager()
                ->remove($userWorkSchedule['schedule']);

            self::$container->get('doctrine')
                ->getManager()
                ->flush();
        }
    }
}
