<?php

namespace App\Controller;

use josegonzalez;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class ApplicationInfoAction
 * @package App\Controller
 */
class ApplicationInfoAction extends AbstractController
{
    /**
     * application version from .env file
     */
    const VERSION = 'GIT_TAG';
    /**
     * @var TokenInterface|null
     */
    private $token;

    /**
     * ApplicationInfo constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->token = $tokenStorage->getToken();
    }

    /**
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $environment = (new josegonzalez\Dotenv\Loader(getcwd().'/.env'))
            ->parse()
            ->toArray();
        return $this->json(array('GIT_TAG' => $environment[ApplicationInfoAction::VERSION]));
    }
}