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
    public const REF_STATUS_OWNER_EDIT = 'WORK-SCHEDULE-REF-STATUS-OWNER-EDIT';

    /**
     * @var string
     */
    public const REF_STATUS_OWNER_ACCEPT = 'WORK-SCHEDULE-REF-STATUS-OWNER-ACCEPT';

    /**
     * @var string
     */
    public const REF_STATUS_MANAGER_ACCEPT = 'WORK-SCHEDULE-REF-STATUS-MANAGER-ACCEPT';

    /**
     * @var string
     */
    public const REF_STATUS_HR_ACCEPT = 'WORK-SCHEDULE-REF-STATUS-HR-ACCEPT';


    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $statuses = [
            self::REF_STATUS_OWNER_EDIT => 'Edytowana przez pracownika',
            self::REF_STATUS_OWNER_ACCEPT => 'Zatwierdzona przez pracownika',
            self::REF_STATUS_MANAGER_ACCEPT => 'Zatwierdzona przez przełożonego',
            self::REF_STATUS_HR_ACCEPT => 'Zatwierdzona przez HR',
        ];

        foreach ($statuses as $key => $value) {
            $userWorkScheduleStatus = new UserWorkScheduleStatus();
            $userWorkScheduleStatus
                ->setId($key)
                ->setName($value)
            ;

            $manager->persist($userWorkScheduleStatus);

            $this->setReference($key, $userWorkScheduleStatus);
        }

        $manager->flush();
    }
}