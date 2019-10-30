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
 *     name="`user_work_schedule_logs`",
 *     indexes={
 *          @ORM\Index(name="idx_user_work_schedule_log_date", columns={"log_date"}),
 *          @ORM\Index(name="idx_user_work_schedule_user_parent", columns={"parent_id"}),
 *          @ORM\Index(name="idx_user_work_schedule_user_owner", columns={"owner_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserWorkScheduleLogRepository")
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
 * @ParentEntity(UserWorkSchedule::class)
 */
class UserWorkScheduleLog extends LogEntity
{
}
