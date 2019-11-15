<?php

namespace App\DataFixtures;

use App\Entity\AbsenceType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class AbsenceTypeFixtures
 * @package App\DataFixtures
 */
class AbsenceTypeFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var int
     */
    public const FIXTURES_RECORD_COUNT = 29;

    /**
     * @var array
     */
    private $absenceTypes = [
        ['UW', 'urlop wypoczynkowy', true],
        ['UR', 'dodatkowy urlop dla niepełnosprawnego', true],
        ['UŻ', 'urlop na żądanie', true],
        ['UO', 'urlop okolicznościowy', true],
        ['UB', 'urlop bezpłatny', true],
        ['OP', 'opieka nad dzieckiem z art. 188 KP', true],
        ['K', 'krwiodawstwo', true],
        ['D', 'delegacja', true],
        ['USZ', 'urlop szkoleniowy', true],
        ['WS', 'wezwanie do sądu/policji/prokuratury', true],
        ['ZP', 'zwolnienie na poszukiwanie pracy', true],
        ['ZW', 'zwolnienie z obowiązku świadczenia pracy', true],
        ['UM', 'urlop macierzyński', true],
        ['UR', 'urlop rodzicielski', true],
        ['UM/UR', 'urlop rodzicielski łączony z pracą', true],
        ['UOC', 'urlop ojcowski', true],
        ['WYCH', 'urlop wychowawczy', true],
        ['ZL', 'zwolnienie lekarskie pracownik', true],
        ['ZL OP', 'zwolnienie lekarskie na chore dziecko/członka rodziny', true],
        ['OP Z', 'opieka nad zdrowym dzieckiem', true],
        ['ŚR', 'świadczenie rehabilitacyjne', true],
        ['NU', 'nieobecność usprawiedliwiona płatna', true],
        ['NP.', 'nieobecność usprawiedliwiona niepłatna', true],
        ['NN', 'nieobecność nieusprawiedliwiona', true],
        ['W5', 'dzień wolny z tytułu 5-dniowego tygodnia pracy', true],
        ['WN', 'niedziela wolna od pracy/ dzień wolny za pracę w niedzielę', true],
        ['WR', 'dzień wolny z harmonogramu', true],
        ['WŚ', 'dzień wolny z tytułu święta', true],
        ['DU', 'do uzupełnienia przez pracownika', true],
    ];

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return array(
            UserSystemFixtures::class,
        );
    }

    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $i = 0;
        foreach ($this->absenceTypes as $absenceType) {
            $newAbsenceType = new AbsenceType();
            $newAbsenceType->setShortName($absenceType[0])
                ->setName($absenceType[1])
                ->setActive($absenceType[2]);

            $manager->persist($newAbsenceType);

            $this->setReference('absence_type_' . $i++, $newAbsenceType);
        }

        $manager->flush();
    }
}
