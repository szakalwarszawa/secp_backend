<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\Entity\Utils\UserAware;
use App\Entity\Types\Model\LogEntity;
use App\Annotations\ParentEntity;

/**
 * @ORM\Table(
 *     schema="logs",
 *     name="`user_timesheet_day_logs`",
 *     indexes={
 *          @ORM\Index(name="idx_user_timesheet_day_log_date", columns={"log_date"}),
 *          @ORM\Index(name="idx_user_timesheet_day_log_log_date", columns={"parent_id", "log_date"}),
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserTimesheetDayLogRepository")
 * @UserAware(
 *     userFieldName="owner_id"
 * )
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
 *
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "parent.id": "exact",
 *      }
 * )
 *
 * @ParentEntity(UserTimesheetDay::class)
 */
class UserTimesheetDayLog extends LogEntity
{
}
