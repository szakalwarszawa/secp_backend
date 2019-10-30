<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Annotations\ParentEntity;
use App\Entity\Types\Model\LogEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     schema="logs",
 *     name="`user_timesheet_logs`",
 *     indexes={
 *          @ORM\Index(name="idx_user_timesheet_log_date", columns={"log_date"}),
 *          @ORM\Index(name="idx_user_timesheet_log_parent", columns={"parent_id"}),
 *          @ORM\Index(name="idx_user_timesheet_log_owner", columns={"owner_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserTimesheetLogRepository")
 * @ApiResource(
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={
 *                      "get"
 *                  }
 *              }
 *          }
 *      },
 *      collectionOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={"get"}
 *              }
 *          }
 *      },
 *      normalizationContext={
 *          "groups"={
 *              "get"
 *          }
 *      }
 * )
 * @ParentEntity(UserTimesheet::class)
 */
class UserTimesheetLog extends LogEntity
{
}
