<?php

namespace App\Tests\Ldap\Updater;

use App\DataFixtures\DepartmentFixtures;
use App\DataFixtures\SectionFixtures;
use App\Entity\Department;
use App\Entity\Section;
use App\Entity\User;
use App\Ldap\Constants\UserAttributes;
use App\Ldap\Import\Updater\DepartmentSectionUpdater;
use App\Ldap\Import\Updater\Result\Collector;
use App\Ldap\Import\Updater\Result\Result;
use App\Ldap\Import\Updater\Result\Types;
use App\Tests\AbstractWebTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use LdapTools\Object\LdapObject;

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
            UserAttributes::SECTION => $this->fixtures
                ->getReference(SectionFixtures::REF_BP_SECTION)->getName(),
            UserAttributes::DEPARTMENT => $this->fixtures
                ->getReference(DepartmentFixtures::REF_DEPARTMENT_BP)->getName(),
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

        $resultsCollector = $userUpdater->getResultsCollector();

        $this->assertInstanceOf(Collector::class, $resultsCollector);
        $counter = $resultsCollector->getCounters();
        $this->assertArrayHasKey(Types::SUCCESS, $counter);
        $this->assertArrayHasKey(Types::FAIL, $counter);

        $this->assertEquals(0, $counter[Types::SUCCESS]);
        $this->assertEquals(0, $counter[Types::FAIL]);

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
        $counter = $resultsCollector->getCounters();

        /**
         * Department 'Nowy department' must be created, must be 'success'
         * Section 'Nowa sekcja' must be created, must be 'success'
         * Department 'BiuroPrezesa' must be updated, must be 'success'
         * Section 'BiuroPrezesa' must be updated, must be 'success'
         */
        $this->assertEquals(4, $counter[Types::SUCCESS]);
        $this->assertEquals(0, $counter[Types::FAIL]);

        /**
         * Last succeed element must be Result::class
         * Last result message must be "Section `SectionFixtures::REF_BP_SECTION` has been updated."
         */
        $succeed = $resultsCollector->getSucceed();
        $lastSectionCreatedSuccess = end($succeed);
        $this->assertInstanceOf(Result::class, $lastSectionCreatedSuccess);
        $this->assertEquals(
            sprintf(
                'Section %s has been updated.',
                $this->fixtures->getReference(SectionFixtures::REF_BP_SECTION)->getName()
            ),
            $lastSectionCreatedSuccess->getMessage()
        );

        $departamentThatShouldExists = $this
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

        $this->assertInstanceOf(Department::class, $departamentThatShouldExists);
        $this->assertInstanceOf(Section::class, $sectionCreatedThatShouldExists);

        $newSectionDepartment = $sectionCreatedThatShouldExists->getDepartment();

        /**
         * Section must be properly assigned to department.
         */
        $this->assertEquals($departamentThatShouldExists, $newSectionDepartment);
    }
}
