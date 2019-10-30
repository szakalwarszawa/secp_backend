<?php

declare(strict_types=1);

namespace App\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Namshi\JOSE\JWS;
use Prophecy\Argument\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\User;
use InvalidArgumentException;

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
     * @var null|string
     */
    private $jwtToken = null;

    /**
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     * @param RequestStack $requestStack
     * @param TokenExtractorInterface $jwtExtractor
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        TokenExtractorInterface $jwtExtractor
    ) {
        if ($requestStack->getCurrentRequest()) {
            $this->jwtToken = $jwtExtractor->extract($requestStack->getCurrentRequest());
        }
        $this->entityManager = $entityManager;
        $this->token = $tokenStorage->getToken();
    }

    /**
     * Extract username from bearer token.
     * It is helpful in routes that are restricted to `IS_AUTHENTICATED_ANONYMOUSLY`.
     * In that case TokenStorage:getUser() contains only 'anon.' string.
     * But if user provided token in request it could be extracted.
     *
     * @param null|string $jwtToken
     *
     * @return string|null
     */
    public function extractUsernameByJwtToken(?string $jwtToken = null): ?string
    {
        $jwtToken = $jwtToken ? $jwtToken : $this->jwtToken;
        if ($jwtToken) {
            try {
                return JWS::load($this->jwtToken)->getPayload()['username'];
            } catch (InvalidArgumentException $exception) {
                return null;
            }
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
                $username = $this->extractUsernameByJwtToken();
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
