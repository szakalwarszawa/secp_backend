<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\UserTimesheetStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class UserTimesheetStatusFixtures
 */
class UserTimesheetStatusFixtures extends Fixture
{
    /**
     * @var string
     */
    public const REF_STATUS_OWNER_EDIT = 'TIMESHEET-REF-STATUS-OWNER-EDIT';

    /**
     * @var string
     */
    public const REF_STATUS_OWNER_ACCEPT = 'TIMESHEET-REF-STATUS-OWNER-ACCEPT';

    /**
     * @var string
     */
    public const REF_STATUS_MANAGER_ACCEPT = 'TIMESHEET-REF-STATUS-MANAGER-ACCEPT';

    /**
     * @var string
     */
    public const REF_STATUS_HR_ACCEPT = 'TIMESHEET-REF-STATUS-HR-ACCEPT';


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
            $userTimesheetStatus = new UserTimesheetStatus();
            $userTimesheetStatus
                ->setId($key)
                ->setName($value)
            ;

            $manager->persist($userTimesheetStatus);

            $this->setReference($key, $userTimesheetStatus);
        }

        $manager->flush();
    }
}
