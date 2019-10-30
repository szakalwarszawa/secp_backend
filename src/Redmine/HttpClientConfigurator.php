<?php

declare(strict_types=1);

namespace App\Redmine;

use App\Entity\AppIssue;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class ClientConfigurator
 */
class HttpClientConfigurator
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * Is Redmine Service enabled.
     *
     * @var bool
     */
    private $redmineServiceStatus;

    /**
     * @var int
     */
    private $reporterCustomFieldId;

    /**
     * @var array
     */
    public $requestOptions;

    /**
     * ClientConfigurator constructor.
     * Set static request options.
     *
     * @param bool $redmineServiceStatus
     * @param string $apiUrl
     * @param null|string $apiKey
     * @param int $projectId
     * @param int $categoryId
     * @param int $trackerId
     * @param int $reporterCustomFieldId
     */
    public function __construct(
        bool $redmineServiceStatus,
        string $apiUrl,
        ?string $apiKey,
        int $projectId,
        int $categoryId,
        int $trackerId,
        int $reporterCustomFieldId
    ) {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
        $this->redmineServiceStatus = $redmineServiceStatus;
        $this->reporterCustomFieldId = $reporterCustomFieldId;

        $this->requestOptions = [
            RedmineRequest::REQUEST_DATA_KEY => [
                'project_id' => $projectId,
                'category_id' => $categoryId,
                'tracker_id' => $trackerId,
                'custom_fields' => []
            ],
        ];
    }

    /**
     * Returns client that has been built based on an entity.
     *
     * @param AppIssue $appIssue
     *
     * @return null|HttpClientInterface
     */
    public function getClientByEntity(AppIssue $appIssue): ?HttpClientInterface
    {
        $issueData = [
            'subject' => $appIssue->getSubject(),
            'description' => $appIssue->getDescription(),
            'reporter_name' => $appIssue->getReporterName(),
        ];

        return $this->makeClient($issueData);
    }

    /**
     * Returns client that has been built based on an array.
     *
     * @param array $issueData
     *
     * @return null|HttpClientInterface
     */
    public function getClientByArray(array $issueData): ?HttpClientInterface
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([
                'subject',
                'description',
                'reporter_name',
            ])
            ->resolve($issueData)
        ;

        return $this->makeClient($issueData);
    }

    /**
     * Makes client based on entry data.
     *
     * @param array $requestData
     *
     * @return null|HttpClientInterface
     */
    private function makeClient(array $requestData): ?HttpClientInterface
    {
        if (!$this->redmineServiceStatus) {
            return null;
        }

        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([
                'subject',
                'description',
                'reporter_name',
            ])
            ->resolve($requestData)
        ;

        if (!empty($requestData)) {
            $this->appendOptions($requestData);
        }

        $this->addCustomFieldData([
            'id' => $this->reporterCustomFieldId,
            'value' => $requestData['reporter_name'],
        ]);

        return HttpClient::create([
            'headers' => [
               'X-Redmine-API-Key' =>  $this->apiKey,
            ],
            'base_uri' => $this->apiUrl,
            'json' => $this->requestOptions,
        ]);
    }

    /**
     * Use default options and append additional data.
     *
     * @param array $additionalData
     *
     * @return void
     */
    private function appendOptions(array $additionalData): void
    {
        $defaultIssueData = $this->requestOptions[RedmineRequest::REQUEST_DATA_KEY];
        $this->requestOptions[RedmineRequest::REQUEST_DATA_KEY] = array_merge($defaultIssueData, $additionalData);
    }

    /**
     * Append custom fields data.
     *
     * @param array $customField
     */
    private function addCustomFieldData(array $customField): void
    {
        $this->requestOptions[RedmineRequest::REQUEST_DATA_KEY]['custom_fields'][] = $customField;
    }
}
