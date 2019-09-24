<?php


namespace App\Tests;

use App\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use Exception;

abstract class AbstractWebTestCase extends WebTestCase
{
    public const HTTP_GET = 'GET';
    public const HTTP_POST = 'POST';
    public const HTTP_PUT = 'PUT';
    public const HTTP_PATCH = 'PATCH';
    public const HTTP_DELETE = 'DELETE';

    public const CONTENT_TYPE_JSON = 'application/json';
    public const CONTENT_TYPE_LD_JSON = 'application/ld+json';
    public const CONTENT_TYPE_XML = 'application/xml';

    public const REF_ADMIN = 'user_admin';
    public const REF_USER = 'user_user';
    public const REF_MANAGER = 'user_manager';

    /**
     * @var ReferenceRepository
     */
    protected static $staticFixtures = null;

    /**
     * @var ClassMetadata
     */
    protected static $staticMetadata = null;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ReferenceRepository
     */
    protected $fixtures;

    /**
     * @var null|TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var null|Security
     */
    protected $security;

    /**
     * @var null|Request
     */
    protected $lastActionRequest;

    /**
     *
     * @throws ToolsException
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $kernel = self::bootKernel();
        $entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        /* @var $entityManager EntityManager */

        if (self::$staticMetadata === null) {
            self::$staticMetadata = $entityManager->getMetadataFactory()->getAllMetadata();

            $schemaTool = new SchemaTool($entityManager);
            $schemaTool->dropDatabase();
            if (!empty(self::$staticMetadata)) {
                $schemaTool->createSchema(self::$staticMetadata);
            }
        }

        if (self::$staticFixtures === null) {
            $fixtures = new Fixtures();
            self::$staticFixtures = $fixtures->getFixtures();
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->fixtures = self::$staticFixtures;
    }

    protected function getEntityFromReference($referenceName): ?object
    {
        if (!$this->fixtures->hasReference($referenceName)) {
            return null;
        }

        $reference = $this->fixtures->getReference($referenceName);

        return $this->entityManager->getRepository(get_class($reference))->find($reference->getId());
    }

    /**
     * @param string $method
     * @param string $route
     * @param string $payload
     * @param array $parameters
     * @param int $expectedStatus
     * @param string $userReference
     * @param string $contentTypeAccept
     * @return null|Response
     * @throws NotFoundReferencedUserException
     */
    protected function getActionResponse(
        $method = self::HTTP_GET,
        $route = '/',
        $payload = null,
        $parameters = [],
        $expectedStatus = 200,
        $userReference = self::REF_ADMIN,
        $contentTypeAccept = self::CONTENT_TYPE_LD_JSON
    ): ?Response {
        $client = $this->makeAuthenticatedClient($userReference);

        $client->request(
            $method,
            $route,
            $parameters,
            [],
            [
                'HTTP_Accept' => $contentTypeAccept,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $this->lastActionRequest = $client->getRequest();

        $this->assertJsonResponse($client->getResponse(), $expectedStatus);

        return $client->getResponse();
    }

    /**
     * @param string $referenceName
     * @return Client
     * @throws NotFoundReferencedUserException
     */
    protected function makeAuthenticatedClient(string $referenceName): Client
    {
        if (!$this->fixtures->hasReference($referenceName)) {
            throw new NotFoundReferencedUserException();
        }

        $this->loginAs($this->fixtures->getReference($referenceName), 'login');
        return $this->makeClient();
    }

    /**
     * @param Response $response
     * @param int $expectedStatusCode
     */
    protected function assertJsonResponse($response, $expectedStatusCode = 200): void
    {
        $this->assertEquals(
            $expectedStatusCode,
            $response->getStatusCode(),
            $response->getContent()
        );

        if (json_decode($response->getContent(), false) !== null) {
            $this->assertTrue(
                $response->headers->contains('Content-Type', 'application/json') ||
                $response->headers->contains('Content-Type', 'application/json; charset=utf-8') ||
                $response->headers->contains('Content-Type', 'application/ld+json; charset=utf-8'),
                $response->headers
            );
        }
    }

    /**
     * @param array $theArray
     * @param string $keyName
     * @param mixed $value
     */
    protected function assertArrayContainsSameKeyWithValue($theArray, $keyName, $value): void
    {
        foreach ($theArray as $arrayItem) {
            if (!array_key_exists($keyName, $arrayItem)) {
                $this->assertTrue(
                    false,
                    sprintf('Array not contains given key: [%s]', $keyName)
                );
            }

            if ($arrayItem->$keyName == $value) {
                $this->assertTrue(true);
                return;
            }
        }

        $this->assertTrue(
            false,
            sprintf('Array not contains given value: [%s => %s]', $keyName, $value)
        );
    }

    /**
     * @param object $listObject
     * @param string $attributeName
     * @param mixed $value
     */
    protected function assertListContainsSameObjectWithValue($listObject, $attributeName, $value): void
    {
        foreach ($listObject as $item) {
            $objectVars = get_class_vars(get_class($item));
            $objectMethods = get_class_methods(get_class($item));

            if (array_key_exists($attributeName, $objectVars)) {
                if ($item->$attributeName == $value) {
                    $this->assertTrue(true);
                    return;
                }
            } elseif (in_array($attributeName, $objectMethods, true)) {
                if ($item->$attributeName() == $value) {
                    $this->assertTrue(true);
                    return;
                }
            } else {
                $this->assertTrue(
                    false,
                    sprintf('Object not contains given attribute: [%s]', $attributeName)
                );
            }
        }

        $this->assertTrue(
            false,
            sprintf('List not contains object with given value: [%s => %s]', $attributeName, $value)
        );
    }

    /**
     * Set $this tokenStorage and Security.
     *
     * @param User $user
     * @param array $roles
     * @param string $providerKey
     *
     * @return void
     */
    protected function loginAsUser(User $user, array $roles = [], string $providerKey = 'secure_area'): void
    {
        if (!empty($roles)) {
            $user->setRoles($roles);
        }

        $security = self::$container->get('security.helper');
        $this->security = $security;

        try {
            $authenticationManager = self::$container->get('security.authentication.manager');
            $token = new PostAuthenticationGuardToken($user, $providerKey, $roles);
            $authenticatedToken = $authenticationManager->authenticate($token);
            $tokenStorage = self::$container->get('security.token_storage');
            $tokenStorage->setToken($authenticatedToken);
            self::$container
                ->get('event_dispatcher')
                ->dispatch(
                    new AuthenticationEvent($authenticatedToken),
                    AuthenticationEvents::AUTHENTICATION_SUCCESS
                );

            $this->tokenStorage = $tokenStorage;
        } catch (Exception $exception) {
        }

        $this->assertNotNull($this->security);
        $this->assertNotNull($this->tokenStorage);
    }
}
