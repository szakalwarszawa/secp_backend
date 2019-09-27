<?php

namespace App\Tests\EventSubscriber;

use App\DataFixtures\UserFixtures;
use App\DataFixtures\UserWorkScheduleStatusFixtures;
use App\Entity\User;
use App\Entity\UserWorkSchedule;
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
                [
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    [
                        date('Y-m-d', strtotime('now -4 days')) => true,
                        date('Y-m-d', strtotime('now -5 days')) => true,
                        date('Y-m-d', strtotime('now -6 days')) => true,
                        date('Y-m-d', strtotime('now -7 days')) => true,
                        date('Y-m-d', strtotime('now -8 days')) => true,
                        date('Y-m-d', strtotime('now -9 days')) => false,
                    ]
                ],
                [
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        date('Y-m-d', strtotime('now -2 days')) => false,
                        date('Y-m-d', strtotime('now -1 days')) => false,
                        date('Y-m-d', strtotime('now +0 days')) => false,
                        date('Y-m-d', strtotime('now +1 days')) => true,
                        date('Y-m-d', strtotime('now +2 days')) => true,
                        date('Y-m-d', strtotime('now +3 days')) => true,
                        date('Y-m-d', strtotime('now +4 days')) => true,
                        date('Y-m-d', strtotime('now +5 days')) => true,
                        date('Y-m-d', strtotime('now +6 days')) => true,
                    ]
                ],
            ]
        ];
      //25 26 |27| 28 29 30 01 02 03
//23 24 25 26 |27| 28
        $testCases[] = [
            [
                [
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    [
                        date('Y-m-d', strtotime('now -2 days')) => true,
                        date('Y-m-d', strtotime('now -1 days')) => true,
                        date('Y-m-d', strtotime('now +0 days')) => true,
                        date('Y-m-d', strtotime('now +1 days')) => false,
                        date('Y-m-d', strtotime('now +2 days')) => true,
                        date('Y-m-d', strtotime('now +3 days')) => true,
                        date('Y-m-d', strtotime('now +4 days')) => true,
                        date('Y-m-d', strtotime('now +5 days')) => true,
                        date('Y-m-d', strtotime('now +6 days')) => true,
                    ]
                ],
                [
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        date('Y-m-d', strtotime('now -4 days')) => false,
                        date('Y-m-d', strtotime('now -3 days')) => false,
                        date('Y-m-d', strtotime('now -2 days')) => false,
                        date('Y-m-d', strtotime('now -1 days')) => false,
                        date('Y-m-d', strtotime('now +0 days')) => false,
                        date('Y-m-d', strtotime('now +1 days')) => true,
                    ]
                ],
            ],
        ];

        //23 24 25 26 |27| 28
        //23 24 25 26 |27| 28
        $testCases[] = [
            [
                [
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    [
                        date('Y-m-d', strtotime('now -4 days')) => true,
                        date('Y-m-d', strtotime('now -3 days')) => true,
                        date('Y-m-d', strtotime('now -2 days')) => true,
                        date('Y-m-d', strtotime('now -1 days')) => true,
                        date('Y-m-d', strtotime('now +0 days')) => true,
                        date('Y-m-d', strtotime('now +1 days')) => false,
                    ]
                ],
                [
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        date('Y-m-d', strtotime('now -4 days')) => false,
                        date('Y-m-d', strtotime('now -3 days')) => false,
                        date('Y-m-d', strtotime('now -2 days')) => false,
                        date('Y-m-d', strtotime('now -1 days')) => false,
                        date('Y-m-d', strtotime('now +0 days')) => false,
                        date('Y-m-d', strtotime('now +1 days')) => true,
                    ]
                ],
            ],
        ];

        // 25 26 |27| 28 29 30 01 02 03
 //23 24 25 26  |27| 28
        //                    01  02 03
        $testCases[] = [
            [
                [
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    [
                        date('Y-m-d', strtotime('now -2 days')) => true,
                        date('Y-m-d', strtotime('now -1 days')) => true,
                        date('Y-m-d', strtotime('now +0 days')) => true,
                        date('Y-m-d', strtotime('now +1 days')) => true,
                        date('Y-m-d', strtotime('now +2 days')) => true,
                        date('Y-m-d', strtotime('now +3 days')) => true,
                        date('Y-m-d', strtotime('now +4 days')) => true,
                        date('Y-m-d', strtotime('now +5 days')) => true,
                        date('Y-m-d', strtotime('now +6 days')) => true,
                    ]
                ],
            ],
            [
                [
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    [
                        date('Y-m-d', strtotime('now -4 days')) => false,
                        date('Y-m-d', strtotime('now -3 days')) => false,
                        date('Y-m-d', strtotime('now -2 days')) => false,
                        date('Y-m-d', strtotime('now -1 days')) => false,
                        date('Y-m-d', strtotime('now +0 days')) => false,
                        date('Y-m-d', strtotime('now +1 days')) => true,
                    ]
                ],
            ],
            [
                [
                    date('Y-m-d', strtotime('now +4 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        date('Y-m-d', strtotime('now +4 days')) => true,
                        date('Y-m-d', strtotime('now +5 days')) => true,
                        date('Y-m-d', strtotime('now +6 days')) => true,
                    ]
                ]
            ]
        ];
//                      29 30 01 02 03
//23 24 25      |27|
        $testCases[] = [
            [
                [
                    date('Y-m-d', strtotime('now +2 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    [
                        date('Y-m-d', strtotime('now +2 days')) => true,
                        date('Y-m-d', strtotime('now +3 days')) => true,
                        date('Y-m-d', strtotime('now +4 days')) => true,
                        date('Y-m-d', strtotime('now +5 days')) => true,
                        date('Y-m-d', strtotime('now +6 days')) => true,
                    ]
                ],
                [
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now -2 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        date('Y-m-d', strtotime('now -2 days')) => false,
                        date('Y-m-d', strtotime('now -3 days')) => false,
                        date('Y-m-d', strtotime('now -4 days')) => false,
                    ]
                ],
            ]
        ];

        //23 24 25 26 |27|
                            //28 29 30
              //25 26
        $testCases [] = [
            [
                [
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +0 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    [
                        date('Y-m-d', strtotime('now -4 days')) => true,
                        date('Y-m-d', strtotime('now -3 days')) => true,
                        date('Y-m-d', strtotime('now -2 days')) => true,
                        date('Y-m-d', strtotime('now -1 days')) => true,
                        date('Y-m-d', strtotime('now -0 days')) => true,
                    ]
                ],
                [
                    date('Y-m-d', strtotime('now +0 days')),
                    date('Y-m-d', strtotime('now +3 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null,
                    [
                        date('Y-m-d', strtotime('now +1 days')) => true,
                        date('Y-m-d', strtotime('now +2 days')) => true,
                        date('Y-m-d', strtotime('now +3 days')) => true,
                    ]
                ],
                [
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now -1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        date('Y-m-d', strtotime('now -2 days')) => false,
                        date('Y-m-d', strtotime('now -1 days')) => false,
                    ]
                ]
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
        // faza I - zakładanie dziewiczych harmonogramów
        foreach ($currentCase as $userScheduleCase) {
            [
                $startFrom,
                $startTo,
                $ownerReferenceName,
                $workScheduleProfileRefName,
                $statusOriginRefName,
                $statusFinalRefName,
                $expectedVisibilities
            ] = $userScheduleCase;

            $schedule = $this->makeUserWorkSchedule(
                $startFrom,
                $startTo,
                $this->getEntityFromReference($ownerReferenceName),
                $this->getEntityFromReference($workScheduleProfileRefName),
                $this->getEntityFromReference($statusOriginRefName)
            );
            $userWorScheduleInTesting[$schedule->getId()] = [
                'schedule' => $schedule,
                'statusOriginRefName' => $statusOriginRefName,
                'statusFinalRefName' => $statusFinalRefName,
                'expectedVisibilities' => $expectedVisibilities,
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
                $scheduleDb->setStatus($this->getEntityFromReference($statusFinalRefName));
                $this->saveToDb($scheduleDb);
            }
        }

        // faza IV - badanie dni po zmianie statusów
        foreach ($userWorScheduleInTesting as $userScheduleCase) {
            $scheduleDb = self::$container->get('doctrine')
                ->getManager()
                ->getRepository(UserWorkSchedule::class)
                ->find($userScheduleCase['schedule']->getId());
            /* @var $scheduleDb UserWorkSchedule */

            foreach ($scheduleDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
                self::$container->get('doctrine')
                    ->getManager()
                    ->refresh($userWorkScheduleDay);

                $scheduleDay = $userWorkScheduleDay->getDayDefinition()->getId();

                if (isset($userScheduleCase['expectedVisibilities'][$scheduleDay])) {
                    $this->assertEquals(
                        $userScheduleCase['expectedVisibilities'][$scheduleDay],
                        $userWorkScheduleDay->getVisibility(),
                        'problem for: ' . $scheduleDay
                    );
                }
            }
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
