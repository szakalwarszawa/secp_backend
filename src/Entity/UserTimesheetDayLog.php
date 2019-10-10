<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\Entity\Types\LogEntityInterface;
use App\Entity\Utils\UserAware;

/**
 * @ORM\Table(
 *     schema="logs",
 *     name="`user_timesheet_day_logs`",
 *     indexes={
 *          @ORM\Index(name="idx_user_timesheet_day_log_date", columns={"log_date"}),
 *          @ORM\Index(name="idx_user_timesheet_day_log_log_date", columns={"user_timesheet_day_id", "log_date"}),
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
 *          "userTimesheetDay.id": "exact",
 *      }
 * )
 */
class UserTimesheetDayLog implements LogEntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserTimesheetDay", inversedBy="userTimesheetDayLogs")
     * @ORM\JoinColumn(nullable=false, columnDefinition="integer")
     * @Groups({"get"})
     */
    private $userTimesheetDay;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get"})
     */
    private $owner;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime")
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
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"get"})
     */
    private $trigger;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return UserTimesheetDay|null
     */
    public function getUserTimesheetDay(): ?UserTimesheetDay
    {
        return $this->userTimesheetDay;
    }

    /**
     * @param UserTimesheetDay|null $userTimesheetDay
     *
     * @return UserTimesheetDayLog
     */
    public function setUserTimesheetDay(?UserTimesheetDay $userTimesheetDay): self
    {
        $this->userTimesheetDay = $userTimesheetDay;

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
     * @return UserTimesheetDayLog
     */
    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getLogDate(): DateTimeInterface
    {
        return $this->logDate;
    }

    /**
     * @param DateTimeInterface $logDate
     *
     * @return UserTimesheetDayLog
     */
    public function setLogDate(DateTimeInterface $logDate): self
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
     * @return UserTimesheetDay
     */
    public function setNotice(string $notice): self
    {
        $this->notice = $notice;

        return $this;
    }

    /**
     * Get trigger
     *
     * @return string|null
     */
    public function getTrigger(): ?string
    {
        return $this->trigger;
    }

    /**
     * Set trigger
     *
     * @param string|null $trigger
     *
     * @return UserTimesheetDayLog
     */
    public function setTrigger(?string $trigger): UserTimesheetDayLog
    {
        $this->trigger = $trigger;

        return $this;
    }
}
