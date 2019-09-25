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
    /**
     * @return array
     */
    public function changeWorkScheduleStatusProvider(): array
    {
        $one = [
            [
                [
                    '2019-09-14',
                    date('Y-m-d', strtotime('now +4 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null
                ]
            ],
            [
                [
                    '2019-09-16',
                    date('Y-m-d', strtotime('now +4 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null
                ]
            ],
            [
                [
                    '2019-09-13',
                    date('Y-m-d', strtotime('now +4 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT
                ]
            ],
        ];

        $two = [
            [
                [
                    '2019-09-13',
                    date('Y-m-d', strtotime('now +4 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null
                ]
            ],
            [
                [
                    '2019-09-16',
                    date('Y-m-d', strtotime('now +4 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null
                ]
            ],
            [
                [
                    '2019-09-14',
                    date('Y-m-d', strtotime('now +4 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT
                ]
            ],
        ];

        $three = [
            [
                [
                    '2019-09-12',
                    date('Y-m-d', strtotime('now +2 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null
                ]
            ],
            [
                [
                    '2019-09-12',
                    date('Y-m-d', strtotime('now +2 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null
                ]
            ],
            [
                [
                    '2019-09-12',
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT
                ]
            ],
        ];

        $four = [
            [
                [
                    date('Y-m-d', strtotime('now +2 days')),
                    date('Y-m-d', strtotime('now +2 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null
                ]
            ],
            [
                [
                    date('Y-m-d', strtotime('now +2 days')),
                    date('Y-m-d', strtotime('now +2 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null
                ]
            ],
            [
                [
                    date('Y-m-d', strtotime('now +2 days')),
                    date('Y-m-d', strtotime('now +1 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT
                ]
            ],
        ];

        $five = [
            [
                [
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now -5 days')),
                    UserFixtures::REF_USER_ADMIN,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null
                ]
            ],
            [
                [
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now -5 days')),
                    UserFixtures::REF_USER_MANAGER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null
                ]
            ],
            [
                [
                    date('Y-m-d', strtotime('now -2 days')),
                    date('Y-m-d', strtotime('now -5 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT
                ]
            ],
        ];

        $six = [
            [
                [
                    '2019-09-14',
                    date('Y-m-d', strtotime('now +4 days')),
                    UserFixtures::REF_USER_USER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null
                ]
            ],
            [
                [
                    '2019-09-16',
                    date('Y-m-d', strtotime('now +4 days')),
                    UserFixtures::REF_USER_ADMIN,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT,
                    null
                ]
            ],
            [
                [
                    '2019-09-13',
                    date('Y-m-d', strtotime('now +4 days')),
                    UserFixtures::REF_USER_MANAGER,
                    'work_schedule_profile_2',
                    UserWorkScheduleStatusFixtures::REF_STATUS_OWNER_EDIT,
                    UserWorkScheduleStatusFixtures::REF_STATUS_HR_ACCEPT
                ]
            ],
        ];

        return array_merge($one, $two, $three, $four, $five, $six);
    }

    /**
     * @test
     * @param array $currentCase
     * @dataProvider changeWorkScheduleStatusProvider
     * @throws Exception
     */
    public function changeWorkScheduleStatus(array $currentCase): void
    {
        $schedule = $this->makeUserWorkSchedule(
            $currentCase[0],
            $currentCase[1],
            $this->getEntityFromReference($currentCase[2]),
            $this->getEntityFromReference($currentCase[3]),
            $this->getEntityFromReference($currentCase[4])
        );

        $scheduleDb = self::$container->get('doctrine')
            ->getManager()
            ->getRepository(UserWorkSchedule::class)
            ->find($schedule->getId());
        /* @var $scheduleDb UserWorkSchedule */
        $userWorkScheduleToClean[] = $scheduleDb;

        if ($currentCase[5] == null) {
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

        foreach ($scheduleDb as $exist) {
            foreach ($exist->getUserWorkScheduleDays() as $userWorkScheduleDay) {

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
        }

        if ($currentCase[5] != null) {
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
        $this->cleanUserWorkSchedule($userWorkScheduleToClean);
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
}
