<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserWorkScheduleDay;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Ldap\Import\LdapImport;

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
    public function __invoke()
    {
        $result = $this
            ->ldapImport
            ->import()
        ;

        return $this->json($result);
    }
}
