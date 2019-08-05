<?php declare(strict_types=1);

namespace App\Security\Authentication\Guard;

use LdapTools\Bundle\LdapToolsBundle\Security\LdapGuardAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

/**
 * Class JsonLdapGuardAuthenticator
 */
class JsonLdapGuardAuthenticator extends LdapGuardAuthenticator
{
    /**
     * @var array
     */
    private $requestContent;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param Request $request
     *
     * @return array|null|false
     */
    private function getContent(Request $request)
    {
        if ($this->requestContent === null) {
            $this->requestContent = json_decode($request->getContent(), true);
        }

        return $this->requestContent;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request): bool
    {
        $content = $this->getContent($request);
        $user = $this
            ->entityManager
            ->getRepository(User::class)
            ->findOneBy([
                'username' => $content['username']
            ]);

        if (null !== $user && $user->getPassword()) {
            return false;
        }

        return $content !== null && $content !== false;
    }

    /**
     * @param Request $request
     * @return null|string
     *
     * @SuppressWarnings("unused")
     */
    protected function getRequestDomain(Request $request): ?string
    {
        return null;
    }

    /**
     * @param string $param
     * @param Request $request
     * @return string|null
     */
    protected function getRequestParameter($param, Request $request): ?string
    {
        $content = $this->getContent($request);

        return $content[$param] ?? null;
            return $content[$param];
        }

        return null;
    }

    /**
     * Set entityManager
     *
     * @param EntityManagerInterface $entityManager
     *
     * @return void
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }
}
