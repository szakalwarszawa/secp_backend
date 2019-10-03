<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\ApplicationInfoAction;
use ApiPlatform\Core\Annotation\ApiProperty;

/**
 * @ApiResource(collectionOperations={
 *  "get",
 *  "retrive-information"={
 *      "access_control"= "is_granted('IS_AUTHENTICATED_FULLY')",
 *      "method"= "GET",
 *      "path"= "/application/info",
 *      "controller" = ApplicationInfoAction::class
 *   }
 * })
 */

final class ApplicationInfo
{
    /**
     * @ApiProperty(identifier=true)
     */
    public $identifier;
}
