<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     schema="dictionary",
 *     name="`work_schedule_profiles`",
 *     indexes={
 *          @ORM\Index(name="idx_work_schedule_profiles_name", columns={"name"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\WorkScheduleProfileRepository")
 * @ORM\HasLifecycleCallbacks()
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
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "id": "exact",
 *          "name": "istart"
 *      }
 * )
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={
 *         "id",
 *         "name"
 *     },
 *     arguments={"orderParameterName"="_order"}
 * )
 */
class WorkScheduleProfile
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", nullable=false, unique=true)
     * @Assert\NotBlank()
     * @Groups({"get", "get-user-with-default_work_schedule_profile"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Groups({"get"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotNull()
     * @Groups({"get"})
     */
    private $notice;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=5,
     *     nullable=false,
     *     columnDefinition="VARCHAR(5) NOT NULL DEFAULT '07:30'",
     *     options={"default"="07:30"}
     * )
     * @Assert\NotNull()
     * @Groups({"get"})
     */
    private $dayStartTimeFrom = '07:30';

    /**
     * @ORM\Column(
     *     type="string",
     *     length=5,
     *     nullable=false,
     *     columnDefinition="VARCHAR(5) NOT NULL DEFAULT '07:30'",
     *     options={"default"="07:30"}
     * )
     * @Assert\NotNull()
     * @Groups({"get"})
     */
    private $dayStartTimeTo = '07:30';

    /**
     * @ORM\Column(
     *     type="string",
     *     length=5,
     *     nullable=false,
     *     columnDefinition="VARCHAR(5) NOT NULL DEFAULT '16:30'",
     *     options={"default"="16:30"}
     * )
     * @Assert\NotNull()
     * @Groups({"get"})
     */
    private $dayEndTimeFrom = '16:30';

    /**
     * @ORM\Column(
     *     type="string",
     *     length=5,
     *     nullable=false,
     *     columnDefinition="VARCHAR(5) NOT NULL DEFAULT '16:30'",
     *     options={"default"="16:30"}
     * )
     * @Assert\NotNull()
     * @Groups({"get"})
     */
    private $dayEndTimeTo = '16:30';

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=4,
     *     scale=2,
     *     nullable=false,
     *     columnDefinition="NUMERIC(4, 2) NOT NULL DEFAULT 8.00",
     *     options={"default"=8.00}
     * )
     * @Assert\NotNull()
     * @Groups({"get"})
     */
    private $dailyWorkingTime = 8.00;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array", nullable=true)
     * @Groups({"get"})
     */
    private $properties = [];

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return WorkScheduleProfile
     */
    public function setName(string $name): WorkScheduleProfile
    {
        $this->name = $name;
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
     * @return WorkScheduleProfile
     */
    public function setNotice(?string $notice): WorkScheduleProfile
    {
        $this->notice = $notice;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDayStartTimeFrom(): ?string
    {
        return $this->dayStartTimeFrom;
    }

    /**
     * @param string $dayStartTimeFrom
     *
     * @return WorkScheduleProfile
     */
    public function setDayStartTimeFrom(string $dayStartTimeFrom): WorkScheduleProfile
    {
        $this->dayStartTimeFrom = $dayStartTimeFrom;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDayStartTimeTo(): ?string
    {
        return $this->dayStartTimeTo;
    }

    /**
     * @param string $dayStartTimeTo
     *
     * @return WorkScheduleProfile
     */
    public function setDayStartTimeTo(string $dayStartTimeTo): WorkScheduleProfile
    {
        $this->dayStartTimeTo = $dayStartTimeTo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDayEndTimeFrom(): ?string
    {
        return $this->dayEndTimeFrom;
    }

    /**
     * @param string $dayEndTimeFrom
     *
     * @return WorkScheduleProfile
     */
    public function setDayEndTimeFrom(string $dayEndTimeFrom): WorkScheduleProfile
    {
        $this->dayEndTimeFrom = $dayEndTimeFrom;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDayEndTimeTo(): ?string
    {
        return $this->dayEndTimeTo;
    }

    /**
     * @param string $dayEndTimeTo
     *
     * @return WorkScheduleProfile
     */
    public function setDayEndTimeTo(string $dayEndTimeTo): WorkScheduleProfile
    {
        $this->dayEndTimeTo = $dayEndTimeTo;

        return $this;
    }

    /**
     * @return float
     */
    public function getDailyWorkingTime(): float
    {
        return (float)$this->dailyWorkingTime;
    }

    /**
     * @param float $dailyWorkingTime
     *
     * @return WorkScheduleProfile
     */
    public function setDailyWorkingTime(float $dailyWorkingTime): WorkScheduleProfile
    {
        $this->dailyWorkingTime = $dailyWorkingTime;

        return $this;
    }

    /**
     * @return null|array
     */
    public function getProperties(): ?array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     *
     * @return WorkScheduleProfile
     */
    public function setProperties(array $properties): WorkScheduleProfile
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @param string $propertyKey
     * @param string $value
     *
     * @return WorkScheduleProfile
     */
    public function addModifyProperty(string $propertyKey, string $value): WorkScheduleProfile
    {
        $this->properties[$propertyKey] = $value;

        return $this;
    }

    /**
     * @param string $propertyKey
     *
     * @return WorkScheduleProfile
     */
    public function removeProperty(string $propertyKey): WorkScheduleProfile
    {
        if ($this->getProperty($propertyKey)) {
            unset($this->properties[$propertyKey]);
        }

        return $this;
    }

    /**
     * Get single property from properties.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getProperty(string $key)
    {
        return $this->properties[$key] ?? null;
    }
}
