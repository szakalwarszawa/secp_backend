<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\UserWorkScheduleStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class UserWorkScheduleStatusFixtures
 */
class UserWorkScheduleStatusFixtures extends Fixture
{
    /**
     * @var string
     */
    public const REF_STATUS_OWNER_EDIT = 'WORK_SCHEDULE_REF_STATUS_OWNER_EDIT';

    /**
     * @var string
     */
    public const REF_STATUS_OWNER_ACCEPT = 'WORK_SCHEDULE_REF_STATUS_OWNER_ACCEPT';

    /**
     * @var string
     */
    public const REF_STATUS_MANAGER_ACCEPT = 'WORK_SCHEDULE_REF_STATUS_MANAGER_ACCEPT';

    /**
     * @var string
     */
    public const REF_STATUS_HR_ACCEPT = 'WORK_SCHEDULE_REF_STATUS_HR_ACCEPT';


    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $statuses = [
            self::REF_STATUS_OWNER_EDIT => 'Edycja właściciela',
            self::REF_STATUS_OWNER_ACCEPT => 'Akceptacja właściciela',
            self::REF_STATUS_MANAGER_ACCEPT => 'Akceptacja przełożonego',
            self::REF_STATUS_HR_ACCEPT => 'Akceptacja pracownika kadr',
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
