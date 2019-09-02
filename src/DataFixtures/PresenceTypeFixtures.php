<?php

namespace App\DataFixtures;

use App\Entity\PresenceType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class PresenceTypeFixtures
 * @package App\DataFixtures
 */
class PresenceTypeFixtures extends Fixture
{
    public const FIXTURES_RECORD_COUNT = 7;

    private $presenceTypes = [
        ['O', 'obecność', false, true, true, 1, 1, PresenceType::RESTRICTION_WORKING_DAY],
        ['HO', 'home office', false, true, true, 4, 4, PresenceType::RESTRICTION_WORKING_AND_NON_WORKING_DAY],
        ['S', 'szkolenie', false, false, true, 0, 0, PresenceType::RESTRICTION_WORKING_DAY],
        ['D', 'delegację', false, true, true, 0, 0, PresenceType::RESTRICTION_WORKING_AND_NON_WORKING_DAY],
        ['N', 'nieobecność', true, false, true, 0, 0, PresenceType::RESTRICTION_WORKING_DAY],
        ['DD', 'dyżur domowy', false, false, true, 0, 0, PresenceType::RESTRICTION_NON_WORKING_DAY],
        ['DP', 'dyżur w pracy', false, false, true, 0, 0, PresenceType::RESTRICTION_WORKING_DAY],
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $i = 0;
        foreach ($this->presenceTypes as $presenceType) {
            $newPresenceType = new PresenceType();
            $newPresenceType->setShortName($presenceType[0])
                ->setName($presenceType[1])
                ->setIsAbsence($presenceType[2])
                ->setIsTimed($presenceType[3])
                ->setActive($presenceType[4])
                ->setCreateRestriction($presenceType[5])
                ->setEditRestriction($presenceType[6])
                ->setWorkingDayRestriction($presenceType[7])
            ;

            $manager->persist($newPresenceType);

            $this->setReference('presence_type_' . $i++, $newPresenceType);
        }

        $manager->flush();
    }
}
