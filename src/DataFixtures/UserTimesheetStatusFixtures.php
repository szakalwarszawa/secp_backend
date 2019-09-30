<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\Data\Statuses;
use App\Entity\UserTimesheetStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class UserTimesheetStatusFixtures
 *
 */
class UserTimesheetStatusFixtures extends Fixture
{
    /**
     * @var string
     */
    public const REF_STATUS_OWNER_EDIT = 'TIMESHEET-STATUS-OWNER-EDIT';

    /**
     * @var string
     */
    public const REF_STATUS_OWNER_ACCEPT = 'TIMESHEET-STATUS-OWNER-ACCEPT';

    /**
     * @var string
     */
    public const REF_STATUS_MANAGER_ACCEPT = 'TIMESHEET-STATUS-MANAGER-ACCEPT';

    /**
     * @var string
     */
    public const REF_STATUS_HR_ACCEPT = 'TIMESHEET-STATUS-HR-ACCEPT';


    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $statuses = Statuses::getAllByClass($this);

        foreach ($statuses as $key => $value) {
            $userTimesheetStatus = new UserTimesheetStatus();
            $userTimesheetStatus
                ->setId($key)
                ->setName($value['title'])
                ->setRules(json_encode($value['rules']))
            ;

            $manager->persist($userTimesheetStatus);

            $this->setReference($key, $userTimesheetStatus);
        }

        $manager->flush();
    }
}
