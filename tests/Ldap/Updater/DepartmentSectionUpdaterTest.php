<?php

namespace App\Tests\Ldap\Updater;

use App\Entity\Department;
use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use LdapTools\Object\LdapObject;
use App\Ldap\Constants\UserAttributes;
use App\Entity\Section;
use App\DataFixtures\SectionFixtures;
use App\DataFixtures\DepartmentFixtures;
use App\Ldap\Import\Updater\DepartmentSectionUpdater;

/**
 * Class DepartmentSectionUpdaterTest
 */
class DepartmentSectionUpdaterTest extends AbstractWebTestCase
{
    /**
     * Test DepartmentSectionUpdater class.
     */
    public function testUpdateDepartmentSection()
    {
        $ldapObjectsCollection = new ArrayCollection();

        $newSection = 'Nowa sekcja';
        $newDepartment = 'Nowy departament';

        /**
         * Section and department does not exist in database yet.
         */
        $ldapObject = new LdapObject([
            'lastname' => 'Jones',
            'firstname' => 'Tommylee',
            'mail' => 'tomylee_jones@parp.gov.pl',
            'dn' => 'CN=Tracz Janusz,OU=ND,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::DEPARTMENT_SHORT => 'ND',
            UserAttributes::POSITION => 'kierownik',
            UserAttributes::SECTION => $newSection,
            UserAttributes::DEPARTMENT => $newDepartment,
            UserAttributes::SUPERVISOR => 'CN=Markowy Marek,OU=DWP,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::SAMACCOUNTNAME => 'tomylee_jones',
        ], 'user');
        $ldapObjectsCollection
            ->add($ldapObject)
        ;

        /**
         * Section and department exists in database.
         * Will be ignored.
         */
        $ldapObject = new LdapObject([
            'lastname' => 'Mate',
            'firstname' => 'Yerba',
            'mail' => 'yerba_mate@parp.gov.pl',
            'dn' => 'CN=Mate Yerba,OU=BP,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::DEPARTMENT_SHORT => 'BP',
            UserAttributes::POSITION => 'dyrektor',
            UserAttributes::SECTION => $this->fixtures->getReference(SectionFixtures::REF_BP_SECION)->getName(),
            UserAttributes::DEPARTMENT => $this->fixtures->getReference(DepartmentFixtures::REF_DEPARTMENT_BP)->getName(),
            UserAttributes::SUPERVISOR => 'CN=Bolton Ramsay,OU=BP,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::SAMACCOUNTNAME => 'yerba_mate',
        ], 'user');
        $ldapObjectsCollection
            ->add($ldapObject)
        ;

        $doctrineRegistry = self::$container->get('doctrine');

        foreach ($ldapObjectsCollection as $ldapObject) {
            $userThatShouldNotExists = $doctrineRegistry
                ->getManager()
                ->getRepository(User::class)
                ->findOneBy([
                    'username' => $ldapObject->get(UserAttributes::SAMACCOUNTNAME)
                ]);
            $this->assertEquals($userThatShouldNotExists, null);
        }

        $userUpdater = new DepartmentSectionUpdater($ldapObjectsCollection, $doctrineRegistry->getManager());

        $this->assertEquals($userUpdater->getSuccessfulCount(), 0);
        $this->assertEquals($userUpdater->getFailedCount(), 0);

        $departamentThatShouldNotExist = $doctrineRegistry
            ->getManager()
            ->getRepository(Department::class)
            ->findOneBy([
                'name' => $newDepartment,
            ]);
        $sectionThatShouldNotExist = $doctrineRegistry
            ->getManager()
            ->getRepository(Section::class)
            ->findOneBy([
                'name' => $newSection,
            ]);

        $this->assertNull($departamentThatShouldNotExist);
        $this->assertNull($sectionThatShouldNotExist);

        $userUpdater->update();

        $this->assertEquals($userUpdater->getSuccessfulCount(), 2);
        $this->assertEquals($userUpdater->getFailedCount(), 0);

        $departamentCreatedThatShouldExists = $doctrineRegistry
            ->getManager()
            ->getRepository(Department::class)
            ->findOneBy([
                'name' => $newDepartment,
            ]);
        $sectionCreatedThatShouldExists = $doctrineRegistry
            ->getManager()
            ->getRepository(Section::class)
            ->findOneBy([
                'name' => $newSection,
            ]);

        $this->assertInstanceOf(Department::class, $departamentCreatedThatShouldExists);
        $this->assertInstanceOf(Section::class, $sectionCreatedThatShouldExists);

        $newSectionDepartment = $sectionCreatedThatShouldExists->getDepartment();

        /**
         * Section must be properly assigned to department.
         */
        $this->assertEquals($newSectionDepartment, $departamentCreatedThatShouldExists);
    }

}
