<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="`user_timesheets`",
 *     indexes={
 *          @ORM\Index(name="idx_user_timesheets_owner_id", columns={"owner_id"}),
 *          @ORM\Index(name="idx_user_timesheets_short_name", columns={"period"}),
 *          @ORM\Index(name="idx_user_timesheets_status", columns={"status"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserTimesheetRepository")
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
class UserTimesheet
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userTimesheets")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get", "post"})
     */
    private $owner;

    /**
     * @ORM\Column(type="string", length=7, nullable=false)
     * @Groups({"get", "post"})
     */
    private $period;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getStatuses")
     * @ORM\Column(type="integer")
     * @Groups({"get", "post", "put"})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserTimesheetDay", mappedBy="userTimesheet")
     * @ApiSubresource()
     */
    private $userTimesheetDays;

    /**
     * UserTimesheet constructor.
     */
    public function __construct()
    {
        $this->userTimesheetDays = new ArrayCollection();
    }

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
     *
     * @return UserTimesheet
     */
    public function setOwner(?User $owner): UserTimesheet
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPeriod(): ?string
    {
        return $this->period;
    }

    /**
     * @param string $period
     *
     * @return UserTimesheet
     */
    public function setPeriod(string $period): UserTimesheet
    {
        $this->period = $period;

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
     * @return UserTimesheet
     */
    public function setStatus(int $status): UserTimesheet
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|UserTimesheetDay[]
     */
    public function getUserTimesheetDays(): Collection
    {
        return $this->userTimesheetDays;
    }

    /**
     * @param UserTimesheetDay $userTimesheetDay
     *
     * @return UserTimesheet
     */
    public function addUserTimesheetDay(UserTimesheetDay $userTimesheetDay): UserTimesheet
    {
        if (!$this->userTimesheetDays->contains($userTimesheetDay)) {
            $this->userTimesheetDays[] = $userTimesheetDay;
            $userTimesheetDay->setUserTimesheet($this);
        }

        return $this;
    }

    /**
     * @param UserTimesheetDay $userTimesheetDay
     *
     * @return UserTimesheet
     */
    public function removeUserTimesheetDay(UserTimesheetDay $userTimesheetDay): UserTimesheet
    {
        if ($this->userTimesheetDays->contains($userTimesheetDay)) {
            $this->userTimesheetDays->removeElement($userTimesheetDay);
            // set the owning side to null (unless already changed)
            if ($userTimesheetDay->getUserTimesheet() === $this) {
                $userTimesheetDay->setUserTimesheet(null);
            }
        }

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
}
