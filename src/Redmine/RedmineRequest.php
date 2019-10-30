<?php

declare(strict_types=1);

namespace App\Redmine;

use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class RedmineRequest
 */
class RedmineRequest implements RedmineRequestInterface
{
    /**
     * Url parameter is required in HttpClientInterface::send() method.
     * Assume that whole url is set already in httpClient.
     * Empty string is considered as null.
     *
     * @var string
     */
    private const DEFAULT_CONFIGURED_URL = '';

    /**
     * Expected response stdClass key containing Redmine API Response values.
     */
    public const REQUEST_DATA_KEY = 'issue';

    /**
     * Send request to redmine.
     *
     * @param null|HttpClientInterface $httpClient
     *
     * @return null|stdClass
     * @throws TransportExceptionInterface
     */
    public function executeClient(?HttpClientInterface $httpClient): ?stdClass
    {
        if (!$httpClient) {
            return null;
        }

        $response =  $httpClient->request(Request::METHOD_POST, self::DEFAULT_CONFIGURED_URL);

        try {
            return json_decode($response->getContent(), false)->{self::REQUEST_DATA_KEY};
        } catch (HttpExceptionInterface $exception) {
            return null;
        }
    }
}
