<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Exception;
use DateTimeImmutable;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Annotations\AnnotatedLogEntity;
use App\Entity\Types\LoggableEntityInterface;
use App\Entity\Utils\UserAware;
use App\Traits\LoggableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\ValueExists;
use App\Validator\TimesheetCompleteness;

/**
 * @ORM\Table(
 *     name="`user_timesheets`",
 *     indexes={
 *          @ORM\Index(name="idx_user_timesheets_owner_id", columns={"owner_id"}),
 *          @ORM\Index(name="idx_user_timesheets_short_name", columns={"period"}),
 *          @ORM\Index(name="idx_user_timesheets_status", columns={"status_id"})
 *     }
 * )
 * @UserAware(userFieldName="owner_id")
 * @ORM\Entity(repositoryClass="App\Repository\UserTimesheetRepository")
 * @ApiResource(
 *      subresourceOperations={
 *          "user_timesheet_logs_get_subresource"= {
 *              "path"="/user_timesheets/{id}/logs"
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={
 *                      "get",
 *                      "UserTimesheet-get-owner-with-department-and-section"
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
 *                  "groups"={
 *                      "get",
 *                      "UserTimesheet-get-owner-with-department-and-section"
 *                  }
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
 *              "get",
 *              "UserTimesheet-get-owner-with-department-and-section"
 *          }
 *      }
 * )
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "id": "exact",
 *          "period": "start",
 *          "status.id": "exact",
 *          "owner.username": "iexact",
 *          "owner.email": "iexact",
 *          "owner.firstName": "istart",
 *          "owner.lastName": "istart",
 *          "owner.department.id": "exact",
 *          "owner.section.id": "exact"
 *      }
 * )
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={
 *         "id",
 *         "period",
 *         "status.id",
 *         "owner.email",
 *         "owner.firstName",
 *         "owner.lastName",
 *         "owner.department.name",
 *         "owner.section.name"
 *     },
 *     arguments={"orderParameterName"="_order"}
 * )
 *
 * @AnnotatedLogEntity(logClass=UserTimesheetLog::class)
 */
class UserTimesheet implements LoggableEntityInterface
{
    use LoggableEntityTrait;

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
     * @var UserTimesheetStatus
     *
     * @Assert\NotBlank()
     * @ValueExists(entity="App\Entity\UserTimesheetStatus", searchField="id")
     * @ORM\ManyToOne(targetEntity="App\Entity\UserTimesheetStatus")
     * @TimesheetCompleteness
     * @Groups({"get", "post", "put"})
     * @AnnotatedLogEntity(options={
     *      "message": "Zmiana statusu z %s na %s"
     * })
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserTimesheetDay", mappedBy="userTimesheet")
     * @ApiSubresource(maxDepth=1)
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
     * @return UserTimesheetStatus|null
     */
    public function getStatus(): ?UserTimesheetStatus
    {
        return $this->status;
    }

    /**
     * @param UserTimesheetStatus $status
     *
     * @return UserTimesheet
     */
    public function setStatus(UserTimesheetStatus $status): UserTimesheet
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
     * Returns this timesheet period range from first to last month day.
     *
     * @return DateTimeImmutable[]
     * @throws Exception
     */
    private function getPeriodRange(): array
    {
        $startDate = new DateTimeImmutable(date($this->period . '-01'));
        $endDate = $startDate->modify('Last day of this month');

        return [
            $startDate,
            $endDate,
        ];
    }

    /**
     * @return DateTimeInterface
     * @throws Exception
     */
    public function getPeriodStartDate(): DateTimeInterface
    {
        return $this->getPeriodRange()[0];
    }

    /**
     * @return DateTimeInterface
     * @throws Exception
     */
    public function getPeriodEndDate(): DateTimeInterface
    {
        return $this->getPeriodRange()[1];
    }
}
