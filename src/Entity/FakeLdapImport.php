<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\LdapImportAction;

/**
 * `Fake entity` class to not disper the configuration at
 *  the same time in the routing and the resource configuration.
 *
 * @ApiResource(itemOperations={
 *     "get",
 *     "ldap_import"={
 *          "access_control"="is_granted('ROLE_ADMIN')",
 *         "method"="GET",
 *         "path"="/ldapimport",
 *         "controller"=LdapImportAction::class,
 *         "read"=false
 *     }
 * })
 */
class FakeLdapImport
{
    /**
     * Empty `fake entity` causes an error.
     *  `There is no PropertyInfo extractor supporting the class`
     *
     * @return void
     */
    public function getId(): void
    {
    }
}
