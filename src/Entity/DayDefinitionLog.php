<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     schema="logs",
 *     name="`day_definition_logs`",
 *     indexes={
 *          @ORM\Index(name="idx_day_definitions_log_date", columns={"log_date"}),
 *          @ORM\Index(name="idx_day_definitions_day_definition_log_date", columns={"day_definition_id", "log_date"}),
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
 */
class DayDefinitionLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DayDefinition", inversedBy="dayDefinitionLogs")
     * @ORM\JoinColumn(nullable=false, columnDefinition="CHAR(10) NOT NULL")
     * @Groups({"get"})
     */
    private $dayDefinition;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get"})
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=10, nullable=false)
     * @Assert\NotBlank()
     * @Groups({"get"})
     */
    private $logDate;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups({"get"})
     */
    private $notice;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return DayDefinition|null
     */
    public function getDayDefinition(): ?DayDefinition
    {
        return $this->dayDefinition;
    }

    /**
     * @param DayDefinition|null $dayDefinition
     *
     * @return DayDefinitionLog
     */
    public function setDayDefinition(?DayDefinition $dayDefinition): self
    {
        $this->dayDefinition = $dayDefinition;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User|null $owner
     *
     * @return DayDefinitionLog
     */
    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogDate(): ?string
    {
        return $this->logDate;
    }

    /**
     * @param string $logDate
     *
     * @return DayDefinitionLog
     */
    public function setLogDate(string $logDate): self
    {
        $this->logDate = $logDate;

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
     * @param string $notice
     *
     * @return DayDefinitionLog
     */
    public function setNotice(string $notice): self
    {
        $this->notice = $notice;

        return $this;
    }
}
