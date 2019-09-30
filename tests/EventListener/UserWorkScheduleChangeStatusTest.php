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
    private $start;
    private $end;
    private $user;
    private $workSchedule;
    private $baseStatus;
    private $endStatus;
    private $days;

    public function __construct($start, $end, $user, $workSchedule, $baseStatus, $endStatus, array $days)
    {
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
        $testCases = [];
        //23 24 25 26 |27| 28
//              25 26 |27| 28 29 30 01 02 03
        $testCases[] = [
            [
                    $object1 = new TestCase(
                         date('Y-m-d', strtotime('now -4 days')),
                         date('Y-m-d', strtotime('now +1 days')),
                        UserFixtures::REF_USER_USER,   'work_schedule_profile_2',
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
                    $object2  = new TestCase(
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
                       //     '-90 days' => false,
                        )
                    )
            ]
        ];
//      //25 26 |27| 28 29 30 01 02 03
////23 24 25 26 |27| 28
//        $testCases[] = [
//            [
//                [
//                    date('Y-m-d', strtotime('now -2 days')),
//                    date('Y-m-d', strtotime('now +6 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    null,
//                    [
//                        date('Y-m-d', strtotime('now -2 days')) => true,
//                        date('Y-m-d', strtotime('now -1 days')) => true,
//                        date('Y-m-d', strtotime('now +0 days')) => true,
//                        date('Y-m-d', strtotime('now +1 days')) => false,
//                        date('Y-m-d', strtotime('now +2 days')) => true,
//                        date('Y-m-d', strtotime('now +3 days')) => true,
//                        date('Y-m-d', strtotime('now +4 days')) => true,
//                        date('Y-m-d', strtotime('now +5 days')) => true,
//                        date('Y-m-d', strtotime('now +6 days')) => true,
//                    ]
//                ],
//                [
//                    date('Y-m-d', strtotime('now -4 days')),
//                    date('Y-m-d', strtotime('now +1 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    [
//                        date('Y-m-d', strtotime('now -4 days')) => false,
//                        date('Y-m-d', strtotime('now -3 days')) => false,
//                        date('Y-m-d', strtotime('now -2 days')) => false,
//                        date('Y-m-d', strtotime('now -1 days')) => false,
//                        date('Y-m-d', strtotime('now +0 days')) => false,
//                        date('Y-m-d', strtotime('now +1 days')) => true,
//                    ]
//                ],
//            ],
//        ];
//
//        //23 24 25 26 |27| 28
//        //23 24 25 26 |27| 28
//        $testCases[] = [
//            [
//                [
//                    date('Y-m-d', strtotime('now -4 days')),
//                    date('Y-m-d', strtotime('now +1 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    null,
//                    [
//                        date('Y-m-d', strtotime('now -4 days')) => true,
//                        date('Y-m-d', strtotime('now -3 days')) => true,
//                        date('Y-m-d', strtotime('now -2 days')) => true,
//                        date('Y-m-d', strtotime('now -1 days')) => true,
//                        date('Y-m-d', strtotime('now +0 days')) => true,
//                        date('Y-m-d', strtotime('now +1 days')) => false,
//                    ]
//                ],
//                [
//                    date('Y-m-d', strtotime('now -4 days')),
//                    date('Y-m-d', strtotime('now +1 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    [
//                        date('Y-m-d', strtotime('now -4 days')) => false,
//                        date('Y-m-d', strtotime('now -3 days')) => false,
//                        date('Y-m-d', strtotime('now -2 days')) => false,
//                        date('Y-m-d', strtotime('now -1 days')) => false,
//                        date('Y-m-d', strtotime('now +0 days')) => false,
//                        date('Y-m-d', strtotime('now +1 days')) => true,
//                    ]
//                ],
//            ],
//        ];
//
//        // 25 26 |27| 28 29 30 01 02 03
// //23 24 25 26  |27| 28
//        //                    01  02 03
//        $testCases[] = [
//            [
//                [
//                    date('Y-m-d', strtotime('now -2 days')),
//                    date('Y-m-d', strtotime('now +6 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    null,
//                    [
//                        date('Y-m-d', strtotime('now -2 days')) => true,
//                        date('Y-m-d', strtotime('now -1 days')) => true,
//                        date('Y-m-d', strtotime('now +0 days')) => true,
//                        date('Y-m-d', strtotime('now +1 days')) => true,
//                        date('Y-m-d', strtotime('now +2 days')) => true,
//                        date('Y-m-d', strtotime('now +3 days')) => true,
//                        date('Y-m-d', strtotime('now +4 days')) => true,
//                        date('Y-m-d', strtotime('now +5 days')) => true,
//                        date('Y-m-d', strtotime('now +6 days')) => true,
//                    ]
//                ],
//            ],
//            [
//                [
//                    date('Y-m-d', strtotime('now -4 days')),
//                    date('Y-m-d', strtotime('now +1 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    null,
//                    [
//                        date('Y-m-d', strtotime('now -4 days')) => false,
//                        date('Y-m-d', strtotime('now -3 days')) => false,
//                        date('Y-m-d', strtotime('now -2 days')) => false,
//                        date('Y-m-d', strtotime('now -1 days')) => false,
//                        date('Y-m-d', strtotime('now +0 days')) => false,
//                        date('Y-m-d', strtotime('now +1 days')) => true,
//                    ]
//                ],
//            ],
//            [
//                [
//                    date('Y-m-d', strtotime('now +4 days')),
//                    date('Y-m-d', strtotime('now +6 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    [
//                        date('Y-m-d', strtotime('now +4 days')) => true,
//                        date('Y-m-d', strtotime('now +5 days')) => true,
//                        date('Y-m-d', strtotime('now +6 days')) => true,
//                    ]
//                ]
//            ]
//        ];
////                      29 30 01 02 03
////23 24 25      |27|
//        $testCases[] = [
//            [
//                [
//                    date('Y-m-d', strtotime('now +2 days')),
//                    date('Y-m-d', strtotime('now +6 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    null,
//                    [
//                        date('Y-m-d', strtotime('now +2 days')) => true,
//                        date('Y-m-d', strtotime('now +3 days')) => true,
//                        date('Y-m-d', strtotime('now +4 days')) => true,
//                        date('Y-m-d', strtotime('now +5 days')) => true,
//                        date('Y-m-d', strtotime('now +6 days')) => true,
//                    ]
//                ],
//                [
//                    date('Y-m-d', strtotime('now -4 days')),
//                    date('Y-m-d', strtotime('now -2 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    [
//                        date('Y-m-d', strtotime('now -2 days')) => false,
//                        date('Y-m-d', strtotime('now -3 days')) => false,
//                        date('Y-m-d', strtotime('now -4 days')) => false,
//                    ]
//                ],
//            ]
//        ];
//
//        //23 24 25 26 |27|
//                            //28 29 30
//              //25 26
//        $testCases [] = [
//            [
//                [
//                    date('Y-m-d', strtotime('now -4 days')),
//                    date('Y-m-d', strtotime('now +0 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    null,
//                    [
//                        date('Y-m-d', strtotime('now -4 days')) => true,
//                        date('Y-m-d', strtotime('now -3 days')) => true,
//                        date('Y-m-d', strtotime('now -2 days')) => true,
//                        date('Y-m-d', strtotime('now -1 days')) => true,
//                        date('Y-m-d', strtotime('now -0 days')) => true,
//                    ]
//                ],
//                [
//                    date('Y-m-d', strtotime('now +0 days')),
//                    date('Y-m-d', strtotime('now +3 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    null,
//                    [
//                        date('Y-m-d', strtotime('now +1 days')) => true,
//                        date('Y-m-d', strtotime('now +2 days')) => true,
//                        date('Y-m-d', strtotime('now +3 days')) => true,
//                    ]
//                ],
//                [
//                    date('Y-m-d', strtotime('now -2 days')),
//                    date('Y-m-d', strtotime('now -1 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    [
//                        date('Y-m-d', strtotime('now -2 days')) => false,
//                        date('Y-m-d', strtotime('now -1 days')) => false,
//                    ]
//                ]
//            ]
//        ];
//
//        //one user does not affect other user
//        //23 24 25 26 |27| 28
//        //23 24 25 26 |27| 28
//        $testCases[] = [
//            [
//                [
//                    date('Y-m-d', strtotime('now -4 days')),
//                    date('Y-m-d', strtotime('now +1 days')),
//                    UserFixtures::REF_USER_USER,
//                    'work_schedule_profile_1',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    null,
//                    [
//                        date('Y-m-d', strtotime('now -4 days')) => true,
//                        date('Y-m-d', strtotime('now -3 days')) => true,
//                        date('Y-m-d', strtotime('now -2 days')) => true,
//                        date('Y-m-d', strtotime('now -1 days')) => true,
//                        date('Y-m-d', strtotime('now +0 days')) => true,
//                        date('Y-m-d', strtotime('now +1 days')) => false,
//                    ]
//                ],
//                [
//                    date('Y-m-d', strtotime('now -4 days')),
//                    date('Y-m-d', strtotime('now +1 days')),
//                    UserFixtures::REF_USER_ADMIN,
//                    'work_schedule_profile_2',
//                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
//                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
//                    [
//                        date('Y-m-d', strtotime('now -4 days')) => false,
//                        date('Y-m-d', strtotime('now -3 days')) => false,
//                        date('Y-m-d', strtotime('now -2 days')) => false,
//                        date('Y-m-d', strtotime('now -1 days')) => false,
//                        date('Y-m-d', strtotime('now +0 days')) => false,
//                        date('Y-m-d', strtotime('now +1 days')) => true,
//                    ]
//                ]
//            ]
//        ];

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
        // faza I - zakładanie dziewiczych harmonogramów

        foreach ($currentCase as $currentCasee) {
            $daysToChange = $currentCasee->getDays();

            $x = array();
            foreach ($daysToChange as $day => $value) {
                $string = 'now ' . $day;
                $key = date('Y-m-d', strtotime($string));
                $val = $value;
                $x[$key] = $val;
            }
            $currentCasee->setDays($x);
        }

        foreach ($currentCase as $userScheduleCase) {
            var_dump($userScheduleCase->getDays());
        }

        exit;
//            [
//                $userScheduleCasee->getStart(),
//                $userScheduleCasee->getEnd(),
//                $userScheduleCasee->getStart(),
////                $userScheduleCasee->getWorkSchedule(),
////                $userScheduleCasee->getBaseStatus(),
////                $userScheduleCasee->getEndStatus(),
////                $userScheduleCasee->getDays(),
////            ] = $userScheduleCasee;
////var_dump($userScheduleCase);
//
//            $schedule = $this->makeUserWorkSchedule(
//                $startFrom,
//                $startTo,
//                $this->getEntityFromReference($ownerReferenceName),
//                $this->getEntityFromReference($workScheduleProfileRefName),
//                $this->getEntityFromReference($statusOriginRefName)
//            );
//            $userWorScheduleInTesting[$schedule->getId()] = [
//                'schedule' => $schedule,
//                'statusOriginRefName' => $statusOriginRefName,
//                'statusFinalRefName' => $statusFinalRefName,
//                'expectedVisibilities' => $expectedVisibilities,
//            ];
//        }

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
                $scheduleDb->setStatus($this->getEntityFromReference($statusFinalRefName));
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

            $days = $scheduleDb->getUserWorkScheduleDays();
            //var_dump($days); exit;
            foreach ($userScheduleCase['expectedVisibilities'] as $scheduleDayId => $expectedVisibility) {


              //  var_dump($scheduleDayId);
                $scheduleDb1 = self::$container->get('doctrine')
                    ->getManager()
                    ->getRepository(UserWorkScheduleDay::class)
                    ->findBy(array('dayDefinition' =>  $scheduleDayId, 'userWorkSchedule' => $userScheduleCase['schedule']));

                $this->assertCount(1, $scheduleDb1,
                    sprintf(
                        "schedule: %s\nday: %s",
                        $userScheduleCase['schedule']->getId(),
                        $scheduleDayId
                    )
                );
var_dump($scheduleDayId);
                self::$container->get('doctrine')
                    ->getManager()
                    ->refresh($scheduleDb1[0]);

                $this->assertEquals(
                    $expectedVisibility,
                    $scheduleDb1[0]->getVisibility(),
                    sprintf(
                        "schedule: %s\nday: %s",
                        $userScheduleCase['schedule']->getId(),
                        $scheduleDayId
                    )
                );
            }


//            foreach ($scheduleDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
//                self::$container->get('doctrine')
//                    ->getManager()
//                    ->refresh($userWorkScheduleDay);
//
//                $scheduleDay = $userWorkScheduleDay->getDayDefinition()->getId();
//
//
//echo $scheduleDay;
//
//
//
//
//
//                if (isset($userScheduleCase['expectedVisibilities'][$scheduleDay])) {
//                    $this->assertEquals(
//                        $userScheduleCase['expectedVisibilities'][$scheduleDay],
//                        $userWorkScheduleDay->getVisibility(),
//                        'problem for: ' . $scheduleDay
//                    );
//                }
//            }
        }

        // faza V - czyszczenie po sobie tabel
        $this->cleanUserWorkSchedule($userWorScheduleInTesting);
        return;

        $scheduleDb = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($schedule->getId());
        /* @var $scheduleDb UserWorkSchedule */
        $userWorkScheduleToClean[] = $scheduleDb;

        if ($currentCase[5] == null) { //nie zmienil sie status sa poprzednie
            foreach ($scheduleDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
                $this->assertTrue($userWorkScheduleDay->getVisibility());
            }
        } else {
            foreach ($scheduleDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
                $this->assertFalse($userWorkScheduleDay->getVisibility());
            }
        }

        if ($currentCase[5] != null) {
            $schedule->setStatus($this->getEntityFromReference($currentCase[5]));
            $this->saveToDb($schedule);
        }

        if ($currentCase[5] != null) { //zmienil sie status ostatni -> warunki ostatniego
            foreach ($scheduleDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
                self::$container->get('doctrine')
                    ->getManager()
                    ->refresh($userWorkScheduleDay);
                if (strtotime($userWorkScheduleDay->getDayDefinition()->getId()) < time()) {
                    $this->assertFalse($userWorkScheduleDay->getVisibility());
                }

                if (strtotime($userWorkScheduleDay->getDayDefinition()->getId()) > time()) {
                    $this->assertTrue($userWorkScheduleDay->getVisibility());
                }
            }
        }
        $this->cleanUserWorkSchedule($userWorScheduleInTesting);
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
