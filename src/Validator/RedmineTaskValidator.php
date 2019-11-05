<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\AppIssue;
use App\Entity\User;
use App\Redmine\HttpClientConfigurator;
use App\Redmine\RedmineRequestInterface;
use App\Utils\UserUtilsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class RedmineTaskValidator
 * This validator is only used to send a query before saving data to the database.
 */
class RedmineTaskValidator extends ConstraintValidator
{
    /**
     * @var HttpClientConfigurator
     */
    private $httpClient;

    /**
     * @var RedmineRequestInterface
     */
    private $redmineRequest;

    /**
     * @var UserUtilsInterface
     */
    private $userUtils;

    /**
     * RedmineTaskValidator constructor.
     *
     * @param RedmineRequestInterface $redmineRequest
     * @param HttpClientConfigurator $httpClient
     * @param UserUtilsInterface $userUtils
     */
    public function __construct(
        RedmineRequestInterface $redmineRequest,
        HttpClientConfigurator $httpClient,
        UserUtilsInterface $userUtils
    ) {
        $this->redmineRequest = $redmineRequest;
        $this->httpClient = $httpClient;
        $this->userUtils = $userUtils;
    }

    /**
     * Is supported type data.
     *
     * @param $value
     *
     * @return bool
     */
    private function supports($value): bool
    {
        return $value instanceof AppIssue;
    }

    /**
     * Redmine report task attempt.
     * This validator does not build violations because regardless
     * of the query result, the log will be saved in the database.
     *
     * If reporter is logged in, post data 'reporterName' will be ignored.
     *
     * @param $entity AppIssue
     * @param Constraint $constraint
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate($entity, Constraint $constraint): void
    {
        if (!$this->supports($entity)) {
            return;
        }

        $currentUser = $this->userUtils->getCurrentUser();
        if ($currentUser instanceof User) {
            $entity->setReporterName($currentUser->getUsername());
        }

        $httpClient = $this->httpClient->getClientByEntity($entity);
        $redmineResponse = $this->redmineRequest->executeClient($httpClient);

        $entity->setRedmineTaskId($redmineResponse->id ?? null);
    }
}
