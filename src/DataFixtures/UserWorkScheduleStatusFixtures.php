<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\UserWorkScheduleStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class UserWorkScheduleStatusFixtures
 */
class UserWorkScheduleStatusFixtures extends Fixture
{
    /**
     * @var string
     */
    public const REF_STATUS_OWNER_EDIT = 'WORK-SCHEDULE-STATUS-OWNER-EDIT';

    /**
     * @var string
     */
    public const REF_STATUS_OWNER_ACCEPT = 'WORK-SCHEDULE-STATUS-OWNER-ACCEPT';

    /**
     * @var string
     */
    public const REF_STATUS_MANAGER_ACCEPT = 'WORK-SCHEDULE-STATUS-MANAGER-ACCEPT';

    /**
     * @var string
     */
    public const REF_STATUS_HR_ACCEPT = 'WORK-SCHEDULE-STATUS-HR-ACCEPT';


    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $statuses = [
            self::REF_STATUS_OWNER_EDIT => [
                'title' => 'Edytowana przez pracownika',
                'rules' => [
                    'ROLE_USER' => [
                        self::REF_STATUS_OWNER_ACCEPT,
                    ],
                    'ROLE_HR' => [
                        self::REF_STATUS_OWNER_ACCEPT,
                        self::REF_STATUS_MANAGER_ACCEPT,
                        self::REF_STATUS_HR_ACCEPT,
                    ],
                ]
            ],
            self::REF_STATUS_OWNER_ACCEPT => [
                'title' => 'Zatwierdzona przez pracownika',
                'rules' => [
                    'ROLE_DEPARTMENT_MANAGER' => [
                        self::REF_STATUS_OWNER_EDIT,
                        self::REF_STATUS_MANAGER_ACCEPT,
                    ],
                    'ROLE_HR' => [
                        self::REF_STATUS_OWNER_EDIT,
                        self::REF_STATUS_MANAGER_ACCEPT,
                        self::REF_STATUS_HR_ACCEPT
                    ],
                ]
            ],
            self::REF_STATUS_MANAGER_ACCEPT => [
                'title' => 'Zatwierdzona przez przełożonego',
                'rules' => [
                    'ROLE_HR' => [
                        self::REF_STATUS_OWNER_EDIT,
                        self::REF_STATUS_OWNER_ACCEPT,
                        self::REF_STATUS_MANAGER_ACCEPT,
                        self::REF_STATUS_HR_ACCEPT,
                    ],
                ],
            ],
            self::REF_STATUS_HR_ACCEPT => [
                'title' => 'Zatwierdzona przez HR',
                'rules' => [
                    'ROLE_HR' => [
                        self::REF_STATUS_OWNER_EDIT,
                        self::REF_STATUS_OWNER_ACCEPT,
                        self::REF_STATUS_MANAGER_ACCEPT,
                    ],
                ],
            ]
        ];

        foreach ($statuses as $key => $value) {
            $userWorkScheduleStatus = new UserWorkScheduleStatus();
            $userWorkScheduleStatus
                ->setId($key)
                ->setName($value['title'])
                ->setRules(json_encode($value['rules']))
            ;

            $manager->persist($userWorkScheduleStatus);

            $this->setReference($key, $userWorkScheduleStatus);
        }

        $manager->flush();
    }
}
