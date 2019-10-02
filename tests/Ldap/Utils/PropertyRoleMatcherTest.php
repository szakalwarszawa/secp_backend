<?php

declare(strict_types=1);

namespace App\Tests\Ldap\Utils;

use App\Entity\User;
use App\Ldap\Constants\UserAttributes;
use App\Tests\AbstractWebTestCase;
use LdapTools\Object\LdapObject;
use App\Ldap\Utils\PropertyRoleMatcher;

/**
 * Class DepartmentSectionUpdaterTest
 */
class PropertyRoleMatcherTest extends AbstractWebTestCase
{
    /**
     * Test PropertyRoleMatcher class.
     *
     * @return void
     */
    public function testPropertyRoleMatcher(): void
    {
        $user = new User();
        $user
            ->setSamAccountName('janusz_tracz')
            ->setUsername('janusz_tracz')
            ->setRoles(['ROLE_USER' , 'ROLE_SUPERVISOR', 'ROLE_TO_REMOVE'])
        ;
        $ldapObject = new LdapObject([
            'lastname' => 'Tracz',
            'firstname' => 'Janusz',
            'mail' => 'janusz_tracz@parp.gov.pl',
            'dn' => 'CN=Tracz Janusz,OU=BI,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::DEPARTMENT_SHORT => 'BI',
            UserAttributes::POSITION => 'kierownik',
            UserAttributes::SECTION => 'Sekcja Kadrowa',
            UserAttributes::DEPARTMENT => 'Biuro Zarządzania Kadrami',
            UserAttributes::SUPERVISOR => 'CN=Markowy Marek,OU=DWP,OU=Zespoly_2016,OU=PARP Pracownicy,DC=test,DC=local',
            UserAttributes::SAMACCOUNTNAME => 'janusz_tracz',
        ], 'user');

        /**
         * User must have 3 roles - ROLE_USER, ROLE_SUPERVISOR, ROLE_TO_REMOVE
         */
        $this->assertEquals(3, count($user->getRoles()));

        $propertyRoleMatcher = new PropertyRoleMatcher($this->entityManager);
        $matcherResult = $propertyRoleMatcher
            ->setBaseLdapObject($ldapObject)
            ->addPropertyBasedRoles($user)
        ;

        $this->assertTrue($matcherResult);

        /**
         * User must have 4 roles - ROLE_USER, ROLE_SUPERVISOR, ROLE_SECTION_MANAGER (due to his position - kierownik), ROLE_HR (due to his department - BZK)
         * Role ROLE_TO_REMOVE will be removed.
         */
        $this->assertEquals(4, count($user->getRoles()));
        $this->assertEquals('ROLE_USER', $user->getRoles()[0]);
        $this->assertEquals('ROLE_SUPERVISOR', $user->getRoles()[1]);
        $this->assertEquals('ROLE_SECTION_MANAGER', $user->getRoles()[2]);
        $this->assertEquals('ROLE_HR', $user->getRoles()[3]);
    }
}
