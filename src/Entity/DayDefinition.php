<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="`day_definitions`",
 *     indexes={
 *          @ORM\Index(name="idx_day_definitions_working_day", columns={"working_day"}),
 *          @ORM\Index(name="idx_day_definitions_date_working_day", columns={"id", "working_day"}),
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\DayDefinitionRepository")
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
 */
class DayDefinition
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=10, nullable=false, unique=true)
     * @Assert\NotBlank()
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups({"get", "put"})
     */
    private $workingDay;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"get", "put"})
     */
    private $notice;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DayDefinitionLog", mappedBy="dayDefinition")
     * @ApiSubresource()
     */
    private $dayDefinitionLogs;

    public function __construct()
    {
        $this->dayDefinitionLogs = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
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
     * @return DayDefinition
     */
    public function setNotice(?string $notice): self
    {
        $this->notice = $notice;

        return $this;
    }

    /**
     * @return Collection|DayDefinitionLog[]
     */
    public function getDayDefinitionLogs(): Collection
    {
        return $this->dayDefinitionLogs;
    }

    /**
     * @param DayDefinitionLog $dayDefinitionLog
     * @return DayDefinition
     */
    public function addDayDefinitionLog(DayDefinitionLog $dayDefinitionLog): self
    {
        if (!$this->dayDefinitionLogs->contains($dayDefinitionLog)) {
            $this->dayDefinitionLogs[] = $dayDefinitionLog;
            $dayDefinitionLog->setDayDefinition($this);
        }

        return $this;
    }

    /**
     * @param DayDefinitionLog $dayDefinitionLog
     * @return DayDefinition
     */
    public function removeDayDefinitionLog(DayDefinitionLog $dayDefinitionLog): self
    {
        if ($this->dayDefinitionLogs->contains($dayDefinitionLog)) {
            $this->dayDefinitionLogs->removeElement($dayDefinitionLog);
            // set the owning side to null (unless already changed)
            if ($dayDefinitionLog->getDayDefinition() === $this) {
                $dayDefinitionLog->setDayDefinition(null);
            }
        }

        return $this;
    }
}
