<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

use App\Entity\User;

class LoginController extends Controller
{
    /**
     * @Rest\Post("/token", name="token_authentication")
     */
    public function newTokenAction(Request $request): JsonResponse
    {
        $username = $request->get('username');
        $password = $request->get('password');
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email'=> $username]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $isValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $password);

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $this->get('lexik_jwt_authentication.encoder')
            ->encode([
                'email' => $user->getUsername(),
                'exp' => time() + 3600 // 1 hour expiration
        ]);

        return new JsonResponse(['token' => $token]);
    }
}
