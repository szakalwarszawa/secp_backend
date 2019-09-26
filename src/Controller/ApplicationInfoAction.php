<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Dotenv\Dotenv;

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
     * application version from .env file
     */
    const COMMIT = 'GIT_HASH';

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
        $dotEnv = new Dotenv();
        $dotEnv->load(getcwd().'/.env');

        $tag = $_ENV[ApplicationInfoAction::VERSION];
        $hash = $_ENV[ApplicationInfoAction::COMMIT];

        return $this->json(
            array(
                'GIT_TAG' => $tag,
                'GIT_HASH' => $hash
            )
        );
    }
}
