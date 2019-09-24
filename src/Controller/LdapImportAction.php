<?php

declare(strict_types=1);

namespace App\Controller;

use App\Ldap\Import\LdapImport;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class LdapImportAction
 */
class LdapImportAction
{
    /**
     * @var LdapImport
     */
    private $ldapImport;

    /**
     * @param LdapImport $ldapImport
     */
    public function __construct(LdapImport $ldapImport)
    {
        $this->ldapImport = $ldapImport;
    }

    /**
     * @return ArrayCollection
     */
    public function __invoke(): ArrayCollection
    {
        $result = $this
            ->ldapImport
            ->import()
        ;

        return $result;
    }
}
