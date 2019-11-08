<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Utils\UserUtilsInterface;
use InvalidArgumentException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Namshi\JOSE\JWS;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class UserUtil
 * Some classes use App\Utils\UserUtil to fetch current user.
 * This class is defined as App\Utils\UserUtilsInterface.
 */
class UserUtil implements UserUtilsInterface
{
    /**
     * @var string
     */
    private const DEFAULT_USER = 'user';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TokenExtractorInterface
     */
    private $tokenExtractor;

    /**
     * UserUtil constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     * @param TokenExtractorInterface $tokenExtractor
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        TokenExtractorInterface $tokenExtractor
    ) {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->tokenExtractor = $tokenExtractor;
    }

    /**
     * Test equivalent of the App\Utils\UserUtil class.
     *
     * @param bool $refreshFromDb - just interface inheritance
     *
     * @return null|User
     *
     * @SuppressWarnings("unused")
     */
    public function getCurrentUser(bool $refreshFromDb = true): ?User
    {
        $usernameFromRequest = $this->getUsernameFromRequestToken();

        if ($usernameFromRequest === UserUtilsInterface::ANONYMOUS_USER) {
            return null;
        }

        return $this
            ->entityManager
            ->getRepository(User::class)
            ->findOneBy([
                'username' => $usernameFromRequest ?? self::DEFAULT_USER,
            ])
        ;
    }

    /**
     * Get token from request header and returns username from token.
     * If header X_ANONYMOUS_REQUEST is present, it returns 'anon.' username.
     *
     * @return string|null
     */
    private function getUsernameFromRequestToken(): ?string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        if ($currentRequest) {
            if ($currentRequest->headers->get('x-anonymous-request')) {
                return UserUtilsInterface::ANONYMOUS_USER;
            }

            $headerToken = $currentRequest->headers->get('authorization');
            try {
                return JWS::load($headerToken)->getPayload()['username'];
            } catch (InvalidArgumentException $exception) {
                return null;
            }
        }

        return null;
    }
}
