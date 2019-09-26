<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(
 *     schema="dictionary",
 *     name="`user_timesheet_statuses`",
 *     indexes={
 *          @ORM\Index(name="idx_user_timsheet_statuses_name", columns={"name"}),
 *     }
 * )
 * @ApiResource(
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={
 *                      "get"
 *                  }
 *              }
 *          },
 *          "put"={
 *              "normalization_context"={
 *                  "groups"={"get"}
 *              },
 *              "denormalization_context"={
 *                  "groups"={"put"}
 *              }
 *          },
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
 * @ORM\Entity()
 */
class UserTimesheetStatus
{
    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @Groups({"get"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"get"})
     */
    protected $name;

    /**
     * @return null|string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return UserTimesheetStatus
     */
    public function setId(string $id): UserTimesheetStatus
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return UserTimesheetStatus
     */
    public function setName(string $name): UserTimesheetStatus
    {
        $this->name = $name;

        return $this;
    }
}
