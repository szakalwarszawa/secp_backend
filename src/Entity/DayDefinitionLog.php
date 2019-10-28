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
 *     name="`day_definition_logs`",
 *     indexes={
 *          @ORM\Index(name="idx_day_definitions_log_date", columns={"log_date"}),
 *          @ORM\Index(name="idx_day_definitions_day_definition_log_parent", columns={"parent_id"}),
 *          @ORM\Index(name="idx_day_definitions_day_definition_log_owner", columns={"owner_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\DayDefinitionLogRepository")
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
 * @ParentEntity(DayDefinition::class)
 */
class DayDefinitionLog extends LogEntity
{
}
