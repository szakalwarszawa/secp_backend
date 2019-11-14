<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Annotations\AnnotatedLogEntity;
use App\Entity\Types\LoggableEntityInterface;
use App\Traits\LoggableEntityTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     schema="dictionary",
 *     name="`day_definitions`",
 *     indexes={
 *          @ORM\Index(name="idx_day_definitions_working_day", columns={"working_day"}),
 *          @ORM\Index(name="idx_day_definitions_date_working_day", columns={"id", "working_day"}),
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\DayDefinitionRepository")
 * @ORM\HasLifecycleCallbacks()
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
 * @AnnotatedLogEntity(logClass=DayDefinitionLog::class)
 */
class DayDefinition implements LoggableEntityInterface
{
    use LoggableEntityTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=10, nullable=false, unique=true, options={"fixed" = true})
     * @Assert\NotBlank()
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups({"get", "put"})
     * @AnnotatedLogEntity(options={
     *      "message": "Zmiana typu dnia pracujący z %s na %s"
     * })
     */
    private $workingDay;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"get", "put"})
     * @AnnotatedLogEntity(options={
     *      "message": "Zmiana opisu z %s na %s"
     * })
     */
    private $notice;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return DayDefinition
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getWorkingDay(): ?bool
    {
        return $this->workingDay;
    }

    /**
     * @param bool $workingDay
     *
     * @return DayDefinition
     */
    public function setWorkingDay(bool $workingDay): self
    {
        $this->workingDay = $workingDay;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotice(): ?string
    {
        return $this->notice;
    }

    /**
     * @param string|null $notice
     *
     * @return DayDefinition
     */
    public function setNotice(?string $notice): self
    {
        $this->notice = $notice;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getAsDateTime(): ?DateTime
    {
        try {
            return new DateTime($this->id);
        } catch (Exception $e) {
            return null;
        }
    }
}
