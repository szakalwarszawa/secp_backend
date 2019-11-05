<?php

declare(strict_types=1);

namespace App\Tests\Redmine;

use App\Entity\AppIssue;
use App\Redmine\HttpClientConfigurator;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class ClientConfiguratorTest
 */
class HttpClientConfiguratorTest extends AbstractWebTestCase
{
    /**
     * @var HttpClientConfigurator|object|null
     */
    private $clientConfigurator;

    /**
     * @var array
     */
    public const SAMPLE_REQUEST_DATA = [
        'subject' => 'testSubject',
        'description' => 'testDescription',
        'reporter_name' => 'testReporter',
    ];

    /**
     * @return void
     */
    public function testGetClientFail(): void
    {
        /**
         * Option `testKey` does not exists.
         */
        $this->expectException(UndefinedOptionsException::class);
        $this->clientConfigurator->getClientByArray([
            'testKey' => 1,
        ]);
    }

    /**
     * @return void
     */
    public function testGetClientByArray(): void
    {
        $requestOptions = $this->clientConfigurator->requestOptions;
        $this->assertArrayHasKey('issue', $requestOptions);

        /**
         * These values will be appended to options in getClient().
         */
        foreach (self::SAMPLE_REQUEST_DATA as $key => $value) {
            $this->assertArrayNotHasKey($key, $requestOptions);
        }

        /**
         * Pass all required options.
         */
        $client = $this->clientConfigurator->getClientByArray(self::SAMPLE_REQUEST_DATA);

        /**
         * It could be
         *  Symfony\Component\HttpClient\NativeHttpClient
         *  Symfony\Component\HttpClient\CurlHttpClient
         */
        $this->assertInstanceOf(HttpClientInterface::class, $client);

        $refreshedOptions = $this->clientConfigurator->requestOptions;

        /**
         * These values ​​should already be saved.
         */
        foreach ($refreshedOptions as $key => $value) {
            $this->assertArrayHasKey($key, $requestOptions);
        }
    }

    /**
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testGetClientByEntity(): void
    {
        $appIssue = new AppIssue();
        $appIssue
            ->setDescription(self::SAMPLE_REQUEST_DATA['description'])
            ->setReporterName(self::SAMPLE_REQUEST_DATA['reporter_name'])
            ->setSubject(self::SAMPLE_REQUEST_DATA['subject'])
        ;

        $this->entityManager->persist($appIssue);
        $this->entityManager->flush();

        $client = $this->clientConfigurator->getClientByEntity($appIssue);

        /**
         * It could be
         *  Symfony\Component\HttpClient\NativeHttpClient
         *  Symfony\Component\HttpClient\CurlHttpClient
         */
        $this->assertInstanceOf(HttpClientInterface::class, $client);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /**
         * Refresh configurator.
         */
        $this->clientConfigurator = self::$container->get(HttpClientConfigurator::class);
        $this->assertInstanceOf(HttpClientConfigurator::class, $this->clientConfigurator);
    }
}
