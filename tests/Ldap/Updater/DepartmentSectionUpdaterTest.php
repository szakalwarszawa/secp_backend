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

        foreach ($ldapObjectsCollection as $ldapObject) {
            $userThatShouldNotExists = $this
                ->entityManager
                ->getRepository(User::class)
                ->findOneBy([
                    'username' => $ldapObject->get(UserAttributes::SAMACCOUNTNAME)
                ]);
            $this->assertEquals(null, $userThatShouldNotExists);
        }

        $userUpdater = new DepartmentSectionUpdater($ldapObjectsCollection, $this->entityManager);

        $this->assertEquals(0, $userUpdater->getSuccessfulCount());
        $this->assertEquals(0, $userUpdater->getFailedCount());

        $departamentThatShouldNotExist = $this
            ->entityManager
            ->getRepository(Department::class)
            ->findOneBy([
                'name' => $newDepartment,
            ]);
        $sectionThatShouldNotExist = $this
            ->entityManager
            ->getRepository(Section::class)
            ->findOneBy([
                'name' => $newSection,
            ]);

        $this->assertNull($departamentThatShouldNotExist);
        $this->assertNull($sectionThatShouldNotExist);

        $userUpdater->update();

        $this->assertEquals(2, $userUpdater->getSuccessfulCount());
        $this->assertEquals(0, $userUpdater->getFailedCount());

        $departamentCreatedThatShouldExists = $this
            ->entityManager
            ->getRepository(Department::class)
            ->findOneBy([
                'name' => $newDepartment,
            ]);
        $sectionCreatedThatShouldExists = $this
            ->entityManager
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
        $this->assertEquals($departamentCreatedThatShouldExists, $newSectionDepartment);
    }

}
