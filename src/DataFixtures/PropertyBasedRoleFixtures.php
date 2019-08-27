<?php

namespace App\DataFixtures;

use App\Entity\PropertyBasedRole;
use App\Ldap\Constants\UserAttributes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PropertyBasedRoleFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $propertyBasedRoles = [
            [
                'ldap_value' => 'kierownik',
                'framework_value' => 'ROLE_SECTION_MANAGER',
                'overridable' => true,
                'based_on' => UserAttributes::POSITION,
            ],
            [
                'ldap_value' => 'dyrektor',
                'framework_value' => 'ROLE_DEPARTMENT_MANAGER',
                'overridable' => true,
                'based_on' => UserAttributes::POSITION,
            ],
            [
                'ldap_value' => 'Biuro Zarządzania Kadrami',
                'framework_value' => 'ROLE_HR',
                'overridable' => true,
                'based_on' => UserAttributes::DEPARTMENT,
            ],
            [
                'ldap_value' => $this->getReference('user_1')->getUsername(),
                'framework_value' => 'ROLE_SUPERVISOR',
                'overridable' => false,
                'based_on' => UserAttributes::SAMACCOUNTNAME,
            ],
            [
                'ldap_value' => $this->getReference('user_2')->getUsername(),
                'framework_value' => 'ROLE_SECRETARY',
                'overridable' => false,
                'based_on' => UserAttributes::SAMACCOUNTNAME,
            ],
        ];

        $i = 0;
        foreach ($propertyBasedRoles as $propertyBasedRole) {
            $object = new PropertyBasedRole();
            $object
                ->setLdapValue($propertyBasedRole['ldap_value'])
                ->setFrameworkValue($propertyBasedRole['framework_value'])
                ->setOverridable($propertyBasedRole['overridable'])
                ->setBasedOn($propertyBasedRole['based_on'])
            ;

            $manager->persist($object);

            $this->addReference('property_based_role_' . $i, $object);
            $i++;
        }

        $manager->flush();
    }
}
