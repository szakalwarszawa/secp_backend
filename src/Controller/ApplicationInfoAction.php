<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Utils\VersionsUtil;

/**
 * Class ApplicationInfoAction
 */
class ApplicationInfoAction extends AbstractController
{
    /**
     * get - git_commit (last commit hash), git_tag (current version), deploy_time (last build datetime)
     *
     * @param VersionsUtil $versionsUtil
     *
     * @return JsonResponse
     */
    public function __invoke(VersionsUtil $versionsUtil): JsonResponse
    {
        return $this->json($versionsUtil->getAll());
    }
}
