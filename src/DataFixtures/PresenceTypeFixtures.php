<?php

namespace App\DataFixtures;

use App\Entity\PresenceType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class PresenceTypeFixtures extends Fixture
{
    private $presenceTypes = [
        ['O', 'obecność', true],
        ['HO', 'home office', true],
        ['S', 'szkolenie', true],
        ['D', 'delegację', true],
        ['N', 'nieobecność', true],
        ['DD', 'dyżur domowy', true],
        ['DP', 'dyżur w pracy', true],
    ];

    public function load(ObjectManager $manager)
    {
        $i = 0;
        foreach ($this->presenceTypes as $presenceType) {
            $newPresenceType = new PresenceType();
            $newPresenceType->setShortName($presenceType[0])
                ->setName($presenceType[1])
                ->setActive($presenceType[2]);

            $manager->persist($newPresenceType);

            $this->setReference('presence_type_' . $i++, $newPresenceType);
        }

        $manager->flush();
    }
}
