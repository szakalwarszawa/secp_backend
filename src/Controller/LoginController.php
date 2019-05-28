<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Ldap\Adapter\ExtLdap\Query;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class LoginController extends Controller
{
    /**
     */
    public function newTokenAction(Request $request): JsonResponse
    {
        $username = $request->get('username');
        $password = $request->get('password');
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $username]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $isValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $password);

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $this->get('lexik_jwt_authentication.encoder')
            ->encode(
                [
                    'email' => $user->getUsername(),
                    'exp' => time() + 3600 // 1 hour expiration
                ]
            );

        $ldap = Ldap::create(
            'ext_ldap',
            [
                'host' => 'plkanalytics.bizmatica.pl',
                'port' => 389,
                'version' => 3,
            ]
        );
        $ldap->bind('ospe_user@egain.local', '1Bizmatica!');
        $ldapQuery = $ldap->query(
            'cn=users,dc=egain,dc=local',
            '(&(CN=*))',
            [
                'scope' => Query::SCOPE_SUB,
                'filter' => [
                    'name',
                    'mail',
                    'initials',
                    'title',
                    'info',
                    'department',
                    'description',
                    'division',
                    'lastlogon',
                    'samaccountname',
                    'manager',
                    'thumbnailphoto',
                    'accountExpires',
                    'useraccountcontrol',
                    'distinguishedName',
                    'cn',
                    'memberOf'
                ],
                'maxItems' => 0,
                'attrsOnly' => 0
            ]
        );

        $ldapResult = $ldapQuery->execute();
        $users = [];
        foreach ($ldapResult->toArray() as $item) {
//            dd($item);
//            $users[] = mb_convert_encoding($item->getAttributes(), 'UTF-8', 'UTF-8');
            $users[] = $item->getAttributes();

        }
//        $ldapQuery = $ldap->query('dc=egain,dc=local', 'sAMAccountName=ospe_user');

//        $ldap = $this->get('Symfony\Component\Ldap\Ldap');

        return new JsonResponse(['token' => $token, 'users_count' => $ldapResult->count(), 'users' => $users]);
    }
}
