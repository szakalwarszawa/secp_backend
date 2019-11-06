<?php

declare(strict_types=1);

namespace App\Tests;

use App\Redmine\RedmineRequestInterface;
use stdClass;

/**
 * Class ApiFakeResponse
 */
class ApiFakeResponse implements RedmineRequestInterface
{
    /**
     * Fake RedmineRequest::executeClient method.
     *
     * @param array $params
     *
     * @return stdClass
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function executeClient(...$params): stdClass
    {
        return (object) [
            'id' => rand(1, 10000),
        ];
    }
}
