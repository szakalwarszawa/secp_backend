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
        ['O', 'obecność', false, true, true],
        ['HO', 'home office', false, true, true],
        ['S', 'szkolenie', false, false, true],
        ['D', 'delegację', false, true, true],
        ['N', 'nieobecność', true, false, true],
        ['DD', 'dyżur domowy', false, false, true],
        ['DP', 'dyżur w pracy', false, false, true],
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
                ->setActive($presenceType[4]);

            $manager->persist($newPresenceType);

            $this->setReference('presence_type_' . $i++, $newPresenceType);
        }

        $manager->flush();
    }
}
