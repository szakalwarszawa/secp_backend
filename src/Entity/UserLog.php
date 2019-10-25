<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Types\Model\LogEntity;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Annotations\ParentEntity;

/**
 * @ORM\Table(
 *     schema="logs",
 *     name="user_logs",
 *     indexes={
 *          @ORM\Index(name="idx_user_log_date", columns={"log_date"}),
 *          @ORM\Index(name="idx_user_log_parent_id", columns={"parent_id"}),
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserLogRepository")
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
 * @ParentEntity(User::class)
 */
class UserLog extends LogEntity
{
}
