<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Ldap\Import\LdapImport;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Ldap\Constants\ArrayResponseFormats;

/**
 * Class LdapImportAction
 */
class LdapImportAction extends AbstractController
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
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $result = $this
            ->ldapImport
            ->setResponseFormat(ArrayResponseFormats::SORTED_SUCCESS_FAILED)
            ->import()
        ;

        return $this->json($result);
    }
}
