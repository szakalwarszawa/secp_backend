<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     schema="logs",
 *     name="`user_work_schedule_logs`",
 *     indexes={
 *          @ORM\Index(name="idx_user_work_schedule_log_date", columns={"log_date"}),
 *          @ORM\Index(name="idx_user_work_schedule_user_work_schedule_log_date", columns={"user_work_schedule_id",
 *          "log_date"}),
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
 */
class UserWorkScheduleLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserWorkSchedule", inversedBy="userWorkScheduleLogs")
     * @ORM\JoinColumn(nullable=false, columnDefinition="INTEGER NOT NULL")
     * @Groups({"get"})
     */
    private $userWorkSchedule;

    /**
     * @return UserWorkSchedule
     */
    public function getUserWorkSchedule(): UserWorkSchedule
    {
        return $this->userWorkSchedule;
    }

    /**
     * @param UserWorkSchedule $userWorkSchedule
     *
     * @return UserWorkScheduleLog
     */
    public function setUserWorkSchedule(UserWorkSchedule $userWorkSchedule): UserWorkScheduleLog
    {
        $this->userWorkSchedule = $userWorkSchedule;

        return $this;
    }

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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return UserWorkScheduleLog
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
     * @return UserWorkScheduleLog
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
     * @return UserWorkScheduleLog
     */
    public function setNotice(string $notice): self
    {
        $this->notice = $notice;

        return $this;
    }
}
