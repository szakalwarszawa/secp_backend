<?php

declare(strict_types=1);

namespace App\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Namshi\JOSE\JWS;
use Prophecy\Argument\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;

/**
 * Class UserUtil
 * Simplified fetching current user than directly injecting TokenStorageInterface.
 */
class UserUtil implements UserUtilsInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     * @param RequestStack $requestStack
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack
    ) {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->token = $tokenStorage->getToken();
    }

    /**
     * Extract username from bearer token.
     * It is helpful in routes that are restricted to `IS_AUTHENTICATED_ANONYMOUSLY`.
     * In that case TokenStorage:getUser() contains only 'anon.' string.
     * But if user provided token in request it could be extracted.
     *
     * @return string|null
     */
    public function extractUsernameFromRequestTokenHeader(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        $authorizationHeader = $request
            ->headers
            ->get('authorization')
            ;

        if (!$authorizationHeader) {
            return null;
        }

        if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            return JWS::load($matches[1])->getPayload()['username'];
        }


        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentUser(bool $refreshFromDb = true): ?User
    {
        $token = $this->token;
        if ($token !== null) {
            if (!$refreshFromDb) {
                return $token->getUser();
            }

            $username = null;
            if ($token->getUser() instanceof User) {
                $username = $token->getUser()->getUsername();
            }

            if ($token->getUser() === UserUtilsInterface::ANONYMOUS_USER) {
                $username = $this->extractUsernameFromRequestTokenHeader();
            }

            if (!$username) {
                return null;
            }

            return $this
                ->entityManager
                ->getRepository(User::class)
                ->findOneBy([
                    'username' => $username,
                ])
            ;
        }

        return null;
    }
}
