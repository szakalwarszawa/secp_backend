<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\Utils\UserAware;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="`user_work_schedules`",
 *     indexes={
 *          @ORM\Index(name="idx_user_work_schedules_status", columns={"status"}),
 *          @ORM\Index(name="idx_user_work_schedules_from_date", columns={"from_date"}),
 *          @ORM\Index(name="idx_user_work_schedules_to_date", columns={"to_date"}),
 *          @ORM\Index(name="idx_user_work_schedules_owner_id", columns={"owner_id"}),
 *          @ORM\Index(name="idx_user_work_schedules_work_schedule_profile_id", columns={"work_schedule_profile_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserWorkScheduleRepository")
 * @UserAware(userFieldName="owner_id")
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
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={"post"}
 *              },
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
class UserWorkSchedule
{
    /*
     * @const int
     */
    public const STATUS_OWNER_EDIT = 0;
    /*
     * @const int
     */
    public const STATUS_OWNER_ACCEPT = 1;
    /*
     * @const int
     */
    public const STATUS_MANAGER_ACCEPT = 2;
    /*
     * @const int
     */
    public const STATUS_HR_ACCEPT = 3;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     * @Assert\Type(type="DateTimeInterface")
     * @Groups({"get", "post"})
     */
    private $fromDate;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     * @Assert\Type(type="DateTimeInterface")
     * @Groups({"get", "post"})
     */
    private $toDate;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getStatuses")
     * @ORM\Column(type="integer")
     * @Groups({"get", "post", "put"})
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get", "post"})
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\WorkScheduleProfile")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get", "post"})
     */
    private $workScheduleProfile;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserWorkScheduleDay", mappedBy="userWorkSchedule", orphanRemoval=true)
     */
    private $userWorkScheduleDays;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserWorkScheduleLog", mappedBy="userWorkSchedule")
     * @ApiSubresource()
     */
    private $userWorkScheduleLogs;

    /**
     * @return mixed
     */
    public function getUserWorkScheduleLogs()
    {
        return $this->userWorkScheduleLogs;
    }

    /**
     * @param mixed $userWorkScheduleLogs
     */
    public function setUserWorkScheduleLogs($userWorkScheduleLogs): void
    {
        $this->userWorkScheduleLogs = $userWorkScheduleLogs;
    }
    /**
     * UserWorkSchedule constructor.
     */
    public function __construct()
    {
        $this->userWorkScheduleDays = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    /**
     * @param \DateTimeInterface $fromDate
     *
     * @return UserWorkSchedule
     */
    public function setFromDate(\DateTimeInterface $fromDate): UserWorkSchedule
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }

    /**
     * @param \DateTimeInterface $toDate
     *
     * @return UserWorkSchedule
     */
    public function setToDate(\DateTimeInterface $toDate): UserWorkSchedule
    {
        $this->toDate = $toDate;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return UserWorkSchedule
     */
    public function setStatus(int $status): UserWorkSchedule
    {
        $this->status = $status;

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
     * @return UserWorkSchedule
     */
    public function setOwner(?User $owner): UserWorkSchedule
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return WorkScheduleProfile|null
     */
    public function getWorkScheduleProfile(): ?WorkScheduleProfile
    {
        return $this->workScheduleProfile;
    }

    /**
     * @param WorkScheduleProfile|null $workScheduleProfile
     *
     * @return UserWorkSchedule
     */
    public function setWorkScheduleProfile(?WorkScheduleProfile $workScheduleProfile): UserWorkSchedule
    {
        $this->workScheduleProfile = $workScheduleProfile;

        return $this;
    }

    /**
     * Return possible statuses, used by status validator
     *
     * @return array
     */
    public function getStatuses(): array
    {
        return [
            self::STATUS_OWNER_EDIT,
            self::STATUS_OWNER_ACCEPT,
            self::STATUS_MANAGER_ACCEPT,
            self::STATUS_HR_ACCEPT,
        ];
    }

    /**
     * @return Collection|UserWorkScheduleDay[]
     */
    public function getUserWorkScheduleDays(): Collection
    {
        return $this->userWorkScheduleDays;
    }

    /**
     * @param UserWorkScheduleDay $userWorkScheduleDay
     *
     * @return UserWorkSchedule
     */
    public function addUserWorkScheduleDay(UserWorkScheduleDay $userWorkScheduleDay): UserWorkSchedule
    {
        if (!$this->userWorkScheduleDays->contains($userWorkScheduleDay)) {
            $this->userWorkScheduleDays[] = $userWorkScheduleDay;
            $userWorkScheduleDay->setUserWorkSchedule($this);
        }

        return $this;
    }

    /**
     * @param UserWorkScheduleDay $userWorkScheduleDay
     *
     * @return UserWorkSchedule
     */
    public function removeUserWorkScheduleDay(UserWorkScheduleDay $userWorkScheduleDay): UserWorkSchedule
    {
        if ($this->userWorkScheduleDays->contains($userWorkScheduleDay)) {
            $this->userWorkScheduleDays->removeElement($userWorkScheduleDay);
            // set the owning side to null (unless already changed)
            if ($userWorkScheduleDay->getUserWorkSchedule() === $this) {
                $userWorkScheduleDay->setUserWorkSchedule(null);
            }
        }

        return $this;
    }
}
