<?php

namespace App\Tests\Ldap\Updater;

use App\Entity\Department;
use App\Entity\User;
use App\Tests\AbstractWebTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use LdapTools\Object\LdapObject;
use App\Ldap\Constants\UserAttributes;
use App\Ldap\Import\Updater\UserUpdater;
use App\Entity\Section;
use App\DataFixtures\SectionFixtures;
use App\DataFixtures\DepartmentFixtures;

/**
 * Class UserUpdaterTest
 */
class UserUpdaterTest extends AbstractWebTestCase
{
    /**
     * Test UserUpdater class.
     */
    public function testUpdateUser()
    {
        /**
         * User that will be manager of BI.SRO Section.
         * Department exists in db.
         * Secion exists in db.
         */
        $ldapObjectsCollection = new ArrayCollection();
        $ldapObjectShouldPass = new LdapObject([
            'lastname' => 'Tracz',
            'firstname' => 'Janusz',
            'mail' => 'janusz_tracz@parp.gov.pl',
            'dn' => 'CN=Tracz Janusz,OU=BI,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::DEPARTMENT_SHORT => 'BI',
            UserAttributes::POSITION => 'kierownik',
            UserAttributes::SECTION => $this->fixtures->getReference(SectionFixtures::REF_BI_SECTION)->getName(),
            UserAttributes::DEPARTMENT => 'Biuro Informatyki',
            UserAttributes::SUPERVISOR => 'CN=Markowy Marek,OU=DWP,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::SAMACCOUNTNAME => 'janusz_tracz',
        ], 'user');
        $ldapObjectsCollection
            ->add($ldapObjectShouldPass)
        ;

        /**
         * User that will be manager of BP Department.
         * Department exists in db.
         * Secion exists in db.
         */
        $ldapObjectShouldPass = new LdapObject([
            'lastname' => 'Mate',
            'firstname' => 'Yerba',
            'mail' => 'yerba_mate@parp.gov.pl',
            'dn' => 'CN=Mate Yerba,OU=BP,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::DEPARTMENT_SHORT => 'BP',
            UserAttributes::POSITION => 'dyrektor',
            UserAttributes::SECTION => $this->fixtures->getReference(SectionFixtures::REF_BP_SECION)->getName(),
            UserAttributes::DEPARTMENT => 'Biuro Prezesa',
            UserAttributes::SUPERVISOR => 'CN=Bolton Ramsay,OU=BP,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::SAMACCOUNTNAME => 'yerba_mate',
        ], 'user');
        $ldapObjectsCollection
            ->add($ldapObjectShouldPass)
        ;

        /**
         * User that will not be saved into database.
         * Department does not exist in database. - Fail reason
         * Section does not exist in database.
         */
        $ldapObjectFailDueToDepartmentIsNotExists = new LdapObject([
            UserAttributes::LAST_NAME => 'Norek',
            UserAttributes::FIRST_NAME => 'Tadeusz',
            UserAttributes::MAIL => 'tadeusz_norek@parp.gov.pl',
            'dn' => 'CN=Norek Tadeusz,OU=BRK,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::DEPARTMENT_SHORT => 'BRK',
            UserAttributes::POSITION => 'specjalista',
            UserAttributes::SECTION => 'Randomowa Sekcja Nie Istniejąca',
            UserAttributes::DEPARTMENT => 'Brak takiego departamentu',
            UserAttributes::SUPERVISOR => 'CN=Guy Random,OU=BP,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::SAMACCOUNTNAME => 'tadeusz_norek',
        ], 'user');
        $ldapObjectsCollection
            ->add($ldapObjectFailDueToDepartmentIsNotExists)
        ;

        /**
         * User that will not be saved into database.
         * This user has no first name defined.
         */
        $ldapObjectFailHasNoFirstName = new LdapObject([
            UserAttributes::LAST_NAME => 'Krawczyk',
            UserAttributes::FIRST_NAME => null,
            UserAttributes::MAIL => 'krzysztof_krawczyk@parp.gov.pl',
            'dn' => 'CN=Krawczyk Krzysztof,OU=BRK,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::DEPARTMENT_SHORT => 'BP',
            UserAttributes::POSITION => 'specjalista',
            UserAttributes::SECTION => $this->fixtures->getReference(SectionFixtures::REF_BP_SECION)->getName(),
            UserAttributes::DEPARTMENT => 'Biuro Prezesa',
            UserAttributes::SUPERVISOR => 'CN=Guy Random,OU=BP,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::SAMACCOUNTNAME => 'krzysztof_krafczyk',
        ], 'user');
        $ldapObjectsCollection
            ->add($ldapObjectFailHasNoFirstName)
        ;

        foreach ($ldapObjectsCollection as $ldapObject) {
            $userThatShouldNotExists = $this
                ->entityManager
                ->getRepository(User::class)
                ->findOneBy([
                    'username' => $ldapObject->get(UserAttributes::SAMACCOUNTNAME)
                ]);
            $this->assertEquals($userThatShouldNotExists, null);
        }

        $userUpdater = new UserUpdater($ldapObjectsCollection, $this->entityManager);

        $this->assertEquals(0, $userUpdater->getSuccessfulCount());
        $this->assertEquals(0, $userUpdater->getFailedCount());

        $userUpdater->update();

        $this->assertEquals(2, $userUpdater->getSuccessfulCount());

        /**
         * 2 is number of failed users.
         * ldapObjectFailHasNoFirstName
         * ldapObjectFailDueToDepartmentIsNotExists
         */
        $this->assertEquals(2, $userUpdater->getFailedCount());

        /**
         * This user should not exist in database.
         */
        $userThatShouldNotExist = $this
            ->entityManager
                ->getRepository(User::class)
                ->findOneBy([
                    'username' => 'tadeusz_norek'
                ]);
        $this->assertNull($userThatShouldNotExist);

        /**
         * Unset failed elements.
         */
        $ldapObjectsCollection->removeElement($ldapObjectFailDueToDepartmentIsNotExists);
        $ldapObjectsCollection->removeElement($ldapObjectFailHasNoFirstName);

        foreach ($ldapObjectsCollection as $ldapObject) {
            $userThatShouldExists = $this
                ->entityManager
                ->getRepository(User::class)
                ->findOneBy([
                    'username' => $ldapObject->get(UserAttributes::SAMACCOUNTNAME)
                ]);

            $this->assertInstanceOf(User::class, $userThatShouldExists);
        }

        $department = $this
            ->entityManager
            ->getRepository(Department::class)
            ->findOneBy([
                'name' => $this->fixtures->getReference(DepartmentFixtures::REF_DEPARTMENT_BP)->getName()
            ]);

        /**
         * Is department `Biuro Prezesa` exists
         */
        $this->assertInstanceOf(Department::class, $department);

        $managerMatch = false;
        foreach ($department->getManagers() as $manager) {
            if ('yerba_mate' === $manager->getUsername()) {
                $managerMatch = true;
            }
        }
         /**
         * Is `yerba_mate` manager of `Biuro Prezesa` department
         */
        $this->assertTrue($managerMatch);

        $section = $this
            ->entityManager
            ->getRepository(Section::class)
            ->findOneBy([
                'name' => $this->fixtures->getReference(SectionFixtures::REF_BI_SECTION)->getName()
            ]);

        $this->assertInstanceOf(Section::class, $section);

        $managerMatch = false;
        foreach ($section->getManagers() as $manager) {
            if ('janusz_tracz' === $manager->getUsername()) {
                $managerMatch = true;
            }
        }

        /**
         * Is `janusz_tracz` manager of `Sekcja Rozwoju Oprogramowania` section
         */
        $this->assertTrue($managerMatch);
    }

}
