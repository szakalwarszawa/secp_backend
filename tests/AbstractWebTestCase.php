<?php


namespace App\Tests;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Response;

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
     * @param array $payload
     * @param int $expectedStatus
     * @param string $userReference
     * @param string $contentTypeAccept
     * @return null|Response
     * @throws NotFoundReferencedUserException
     */
    protected function getActionResponse(
        $method = self::HTTP_GET,
        $route = '/',
        $payload = [],
        $expectedStatus = 200,
        $userReference = self::REF_ADMIN,
        $contentTypeAccept = self::CONTENT_TYPE_JSON
    ): ?Response
    {
        $client = $this->makeAuthenticatedClient($userReference);

        $client->request(
            $method,
            $route,
            $payload,
            [],
            ['HTTP_Accept' => $contentTypeAccept]
        );

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
}
