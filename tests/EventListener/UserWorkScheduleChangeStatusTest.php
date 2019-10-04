<?php

declare(strict_types=1);

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
use App\Tests\Utils\UserWorkScheduleChangeStatusTestCase;

/**
 * Class UserWorkScheduleChangeStatusTest
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
                new UserWorkScheduleChangeStatusTestCase(
                    'first is starting earlier, first schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    '',
                    [
                        '-4 days' => true,
                        '-3 days' => true,
                        '-2 days' => true,
                        '-1 days' => true,
                        '+0 days' => true,
                        '+1 days' => false
                    ]
                ),
                new UserWorkScheduleChangeStatusTestCase(
                    'first is starting earlier, second schedule',
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        '-2 days' => false,
                        '-1 days' => false,
                        '+0 days' => false,
                        '+1 days' => true,
                        '+2 days' => true,
                        '+3 days' => true,
                        '+4 days' => true,
                        '+5 days' => true,
                        '+6 days' => true,
                    ]
                )
            ]
        ];
        //25 26 |27| 28 29 30 01 02 03
//23 24 25 26 |27| 28
        $testCases[] = [
            [
                new UserWorkScheduleChangeStatusTestCase(
                    'second is starting earlier, first schedule',
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    '',
                    [
                        '-2 days' => true,
                        '-1 days' => true,
                        '+0 days' => true,
                        '+1 days' => false,
                        '+2 days' => true,
                        '+3 days' => true,
                        '+4 days' => true,
                        '+5 days' => true,
                        '+6 days' => true,
                    ]
                ),
                new UserWorkScheduleChangeStatusTestCase(
                    'second is starting earlier, second schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        '-4 days' => false,
                        '-3 days' => false,
                        '-2 days' => false,
                        '-1 days' => false,
                        '+0 days' => false,
                        '+1 days' => true,
                    ]
                )
            ]
        ];
//
//        //23 24 25 26 |27| 28
//        //23 24 25 26 |27| 28
        $testCases[] = [
            [
                new UserWorkScheduleChangeStatusTestCase(
                    'both are equal, first schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    '',
                    [
                        '-4 days' => true,
                        '-3 days' => true,
                        '-2 days' => true,
                        '-1 days' => true,
                        '+0 days' => true,
                        '+1 days' => false,
                    ]
                ),
                new UserWorkScheduleChangeStatusTestCase(
                    'both are equal, second schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        '-4 days' => false,
                        '-3 days' => false,
                        '-2 days' => false,
                        '-1 days' => false,
                        '+0 days' => false,
                        '+1 days' => true,
                    ]
                )
            ]
        ];

//         25 26 |27| 28 29 30 01 02 03
        //23 24 25 26  |27| 28
        //                         01  02 03
        $testCases[] = [
            [
                new UserWorkScheduleChangeStatusTestCase(
                    'third only future, first schedule',
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    '',
                    [
                        '-2 days' => true,
                        '-1 days' => true,
                        '+0 days' => true,
                        '+1 days' => false,
                        '+2 days' => true,
                        '+3 days' => true,
                        '+4 days' => false,
                        '+5 days' => false,
                        '+6 days' => false,
                    ]
                ),

                new UserWorkScheduleChangeStatusTestCase(
                    'third only future, second schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        '-4 days' => false,
                        '-3 days' => false,
                        '-2 days' => false,
                        '-1 days' => false,
                        '+0 days' => false,
                        '+1 days' => true,
                    ]
                ),

                new UserWorkScheduleChangeStatusTestCase(
                    'third only future, third schedule',
                    date('Y-m-d', strtotime('now +4 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        '+4 days' => true,
                        '+5 days' => true,
                        '+6 days' => true,
                    ]
                )
            ]
        ];
////                      29 30 01 02 03
////23 24 25      |27|
        $testCases[] = [
            [
                new UserWorkScheduleChangeStatusTestCase(
                    'gaps on both sides, first schedule',
                    date('Y-m-d', strtotime('now +2 days')),
                    date('Y-m-d', strtotime('now +6 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    '',
                    [
                        '+2 days' => true,
                        '+3 days' => true,
                        '+4 days' => true,
                        '+5 days' => true,
                        '+6 days' => true,
                    ]
                ),
                new UserWorkScheduleChangeStatusTestCase(
                    'gaps on both sides, second schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now -2 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        '-2 days' => false,
                        '-3 days' => false,
                        '-4 days' => false,
                    ]
                ),
            ]
        ];
//
//        //23 24 25 26 |27|
//                            //28 29 30
//              //25 26
        $testCases [] = [
            [
                new UserWorkScheduleChangeStatusTestCase(
                    'three at once, first schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +0 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    '',
                    [
                        '-4 days' => true,
                        '-3 days' => true,
                        '-2 days' => true,
                        '-1 days' => true,
                        '-0 days' => true,
                    ]
                ),
                new UserWorkScheduleChangeStatusTestCase(
                    'three at once, second schedule',
                    date('Y-m-d', strtotime('now +0 days')),
                    date('Y-m-d', strtotime('now +3 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    '',
                    [
                        '+1 days' => true,
                        '+2 days' => true,
                        '+3 days' => true,
                    ]
                ),
                new UserWorkScheduleChangeStatusTestCase(
                    'three at once, third schedule',
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now -1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        '-2 days' => false,
                        '-1 days' => false,
                    ]
                )
            ]
        ];

        //one user does not affect other user
        //23 24 25 26 |27| 28
        //23 24 25 26 |27| 28
        $testCases[] = [
            [
                new UserWorkScheduleChangeStatusTestCase(
                    'one does not affect each others, first schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_1',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    '',
                    [
                        '-4 days' => true,
                        '-3 days' => true,
                        '-2 days' => true,
                        '-1 days' => true,
                        '+0 days' => true,
                        '+1 days' => false,
                    ]
                ),
                new UserWorkScheduleChangeStatusTestCase(
                    'one does not affect each others, second schedule',
                    date('Y-m-d', strtotime('now -4 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_ADMIN,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    [
                        '-4 days' => false,
                        '-3 days' => false,
                        '-2 days' => false,
                        '-1 days' => false,
                        '+0 days' => false,
                        '+1 days' => true,
                    ]
                )
            ]
        ];

        return $testCases;
    }

    /**
     * @test
     * @param array $currentCase
     * @dataProvider changeWorkScheduleStatusProvider
     *
     * @return void
     * @throws Exception
     */
    public function changeWorkScheduleStatus(array $currentCases): void
    {
        $user = $this->getEntityFromReference(UserFixtures::REF_USER_ADMIN);
        $this->loginAsUser($user, ['ROLE_ADMIN']);

        $userWorScheduleInTesting = [];
        // faza I - zakładanie dziewiczych harmonogramów oraz budowa odpowiednich dat ze stringow
        foreach ($currentCases as $currentCase) {
            $daysToFormat = $currentCase->getDays();
            $currentCase->setPreformattedDays($daysToFormat);
            $builtDates = array();
            foreach ($daysToFormat as $day => $value) {
                $dateString = 'now ' . $day;
                $key = date('Y-m-d', strtotime($dateString));
                $builtDates[$key] = $value;
            }
            $currentCase->setDays($builtDates);
        }

        foreach ($currentCases as $userScheduleCase) {
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
                'expectedDeleted' => $userScheduleCase->getDays(),
                'preFormatted' => $userScheduleCase->getPreformattedDays(),
                'name' => $userScheduleCase->getName()
            ];
        }

        // faza II - sprawdzanie czy dni harmonogramów maja poprawnie ustawioną flagę "deleted"
        foreach ($userWorScheduleInTesting as $userScheduleCase) {
            $scheduleDb = $this->entityManager->getRepository(UserWorkSchedule::class)
                ->find($userScheduleCase['schedule']->getId());
            /* @var $scheduleDb UserWorkSchedule */

            if ($userScheduleCase['statusOriginRefName'] === UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT) {
                foreach ($scheduleDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
                    $this->assertTrue($userWorkScheduleDay->isDeleted());
                }
            } else {
                foreach ($scheduleDb->getUserWorkScheduleDays() as $userWorkScheduleDay) {
                    $this->assertFalse($userWorkScheduleDay->isDeleted());
                }
            }
        }

        // faza III - zmiany statusu harmonogramów
        foreach ($userWorScheduleInTesting as $userScheduleCase) {
            $scheduleDb = $this->entityManager->getRepository(UserWorkSchedule::class)
                ->find($userScheduleCase['schedule']->getId());
            /* @var $scheduleDb UserWorkSchedule */

            if ($userScheduleCase['statusFinalRefName'] !== '') {
                $scheduleDb->setStatus(
                    $this->getEntityFromReference(UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT)
                );
                $this->saveToDb($scheduleDb);
            }
        }

        // faza IV - badanie dni po zmianie statusów
        foreach ($userWorScheduleInTesting as $userScheduleCase) {
            //czy dzien w ogole wystepuje, zbadac czy true albo false
            $scheduleDb = $this->entityManager->getRepository(UserWorkSchedule::class)
                ->find($userScheduleCase['schedule']->getId());
            /* @var $scheduleDb UserWorkSchedule */

            $badCaseCounter = 0;

            foreach ($userScheduleCase['expectedDeleted'] as $scheduleDayId => $expectedDeleted) {
                $scheduleDb1 = $this->entityManager->getRepository(UserWorkScheduleDay::class)
                    ->findBy(['dayDefinition' => $scheduleDayId, 'userWorkSchedule' => $userScheduleCase['schedule']]);

                $keys = array_keys($userScheduleCase['preFormatted']);

                $this->assertCount(
                    1,
                    $scheduleDb1,
                    sprintf(
                        'schedule: %s\nday: %s\ncase: %s',
                        $userScheduleCase['schedule']->getId(),
                        $scheduleDayId,
                        $keys[$badCaseCounter]
                    )
                );

                $this->entityManager->refresh($scheduleDb1[0]);

                $this->assertEquals(
                    $expectedDeleted,
                    $scheduleDb1[0]->isDeleted(),
                    sprintf(
                        'schedule: %s\nday: %s\ncase: %s \nlabel: %s',
                        $userScheduleCase['schedule']->getId(),
                        $scheduleDayId,
                        $keys[$badCaseCounter],
                        $userScheduleCase['name']
                    )
                );
                $badCaseCounter++;
            }
        }

        // faza V - czyszczenie harmonogramów
        $this->cleanUserWorkSchedule($userWorScheduleInTesting);
    }

    /**
     * @param string $startFrom
     * @param string $startTo
     * @param User $owner
     * @param WorkScheduleProfile $workScheduleProfile
     * @param UserWorkScheduleStatus $status
     *
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
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function saveToDb($entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * @param array $userWorkSchedules
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function cleanUserWorkSchedule(array $userWorkSchedules): void
    {
        foreach ($userWorkSchedules as $userWorkSchedule) {
            $this->entityManager->remove($userWorkSchedule['schedule']);
        }

        $this->entityManager->flush();
    }
}
