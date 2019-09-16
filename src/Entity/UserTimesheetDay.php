<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\UserCreateTimesheetDayAction;
use App\Controller\UserOwnTimesheetDayAction;
use App\Entity\Utils\UserAware;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(
 *     name="`user_timesheet_days`",
 *     indexes={
 *          @ORM\Index(name="idx_user_timesheet_days_absence_type_id", columns={"absence_type_id"}),
 *          @ORM\Index(name="idx_user_timesheet_days_presence_type_id", columns={"presence_type_id"}),
 *          @ORM\Index(name="idx_user_timesheet_days_user_timesheet_id", columns={"user_timesheet_id"}),
 *          @ORM\Index(name="idx_user_timesheet_days_user_work_schedule_day_id", columns={"user_work_schedule_day_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserTimesheetDayRepository")
 * @UserAware(
 *     troughReferenceTable="user_timesheets",
 *     troughForeignKey="user_timesheet_id",
 *     troughReferenceId="id",
 *     userFieldName="owner_id"
 * )
 * @ApiResource(
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={
 *                      "get",
 *                      "get-user-timesheet-day-with-user_work_schedule_day"
 *                  }
 *              }
 *          },
 *          "put"={
 *              "normalization_context"={
 *                  "groups"={
 *                      "get",
 *                      "get-user-timesheet-day-with-user_work_schedule_day"
 *                  }
 *              },
 *              "denormalization_context"={
 *                  "groups"={
 *                      "put"
 *                  }
 *              }
 *          }
 *      },
 *      collectionOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={
 *                      "get",
 *                      "get-user-timesheet-day-with-user_work_schedule_day",
 *                      "get-user-timesheet-day-with-user-timesheet"
 *                  }
 *              }
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={"post"}
 *              },
 *              "normalization_context"={
 *                  "groups"={
 *                      "get",
 *                      "get-user-timesheet-day-with-user_work_schedule_day"
 *                  }
 *              }
 *          },
 *          "post-users-create-timesheet-day"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "method"="POST",
 *              "path"="/user_timesheet_days/own/create/{day}",
 *              "requirements"={"day"="\d{4}-\d{2}-\d{2}"},
 *              "controller"=UserCreateTimesheetDayAction::class,
 *              "denormalization_context"={
 *                  "groups"={"post"}
 *              },
 *              "normalization_context"={
 *                  "groups"={
 *                      "get",
 *                      "get-user-timesheet-day-with-user_work_schedule_day"
 *                  }
 *              }
 *          },
 *          "get-users-own-timesheet-days"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "method"="GET",
 *              "path"="/user_timesheet_days/own/{dateFrom}/{dateTo}",
 *              "requirements"={"dateFrom"="\d{4}-\d{2}-\d{2}", "dateTo"="\d{4}-\d{2}-\d{2}"},
 *              "controller"=UserOwnTimesheetDayAction::class,
 *              "normalization_context"={
 *                  "groups"={
 *                      "get",
 *                      "get-user-timesheet-day-with-user_work_schedule_day"
 *                  }
 *              }
 *          }
 *      },
 *      normalizationContext={
 *          "groups"={
 *              "get",
 *              "get-user-timesheet-day-with-user_work_schedule_day"
 *          }
 *      }
 * )
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "id": "exact",
 *          "userTimesheet.period": "istart",
 *          "userTimesheet.owner.username": "iexact",
 *          "userTimesheet.owner.email": "iexact",
 *          "userTimesheet.owner.firstName": "istart",
 *          "userTimesheet.owner.lastName": "istart",
 *          "userWorkScheduleDay.dayDefinition.id": "istart",
 *          "presenceType.id": "exact",
 *          "absenceType.id": "exact",
 *          "dayStartTime": "exact",
 *          "dayEndTime": "exact",
 *          "workingTime": "exact"
 *      }
 * )
 * @ApiFilter(
 *     RangeFilter::class,
 *     properties={
 *         "userWorkScheduleDay.dayDefinition.id"
 *     }
 * )
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={
 *         "id",
 *         "userTimesheet.period",
 *         "userTimesheet.owner.firstName",
 *         "userTimesheet.owner.lastName",
 *         "userWorkScheduleDay.dayDefinition.id",
 *         "presenceType.name",
 *         "absenceType.name",
 *         "dayStartTime",
 *         "dayEndTime",
 *         "workingTime"
 *     },
 *     arguments={"orderParameterName"="_order"}
 * )
 */
class UserTimesheetDay
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserTimesheet", inversedBy="userTimesheetDays")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get", "post", "get-user-timesheet-day-with-user-timesheet"})
     */
    private $userTimesheet;

    /**
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\UserWorkScheduleDay",
     *     inversedBy="userTimesheetDay",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"post", "get-user-timesheet-day-with-user_work_schedule_day"})
     */
    private $userWorkScheduleDay;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Groups({"get", "post", "put"})
     */
    private $dayStartTime;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     * @Groups({"get", "post", "put"})
     */
    private $dayEndTime;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2)
     * @Groups({"get", "post", "put"})
     */
    private $workingTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PresenceType")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get", "post", "put"})
     */
    private $presenceType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AbsenceType")
     * @Groups({"hr:output", "current_user_is_owner", "put", "post"})
     */
    private $absenceType;

    /**
     * @var string
     */
    private $dayDate;

    /**
     * @return string|null
     */
    public function getDayDate(): ?string
    {
        return $this->dayDate;
    }

    /**
     * @param string|null $dayDate
     *
     * @return UserTimesheetDay
     */
    public function setDayDate(?string $dayDate): UserTimesheetDay
    {
        $this->dayDate = $dayDate;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return UserTimesheet|null
     */
    public function getUserTimesheet(): ?UserTimesheet
    {
        return $this->userTimesheet;
    }

    /**
     * @param UserTimesheet|null $userTimesheet
     *
     * @return UserTimesheetDay
     */
    public function setUserTimesheet(?UserTimesheet $userTimesheet): UserTimesheetDay
    {
        $this->userTimesheet = $userTimesheet;

        return $this;
    }

    /**
     * @return UserWorkScheduleDay|null
     */
    public function getUserWorkScheduleDay(): ?UserWorkScheduleDay
    {
        return $this->userWorkScheduleDay;
    }

    /**
     * @param UserWorkScheduleDay $userWorkScheduleDay
     *
     * @return UserTimesheetDay
     */
    public function setUserWorkScheduleDay(UserWorkScheduleDay $userWorkScheduleDay): UserTimesheetDay
    {
        $this->userWorkScheduleDay = $userWorkScheduleDay;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDayStartTime(): ?string
    {
        return $this->dayStartTime;
    }

    /**
     * @param string|null $dayStartTime
     *
     * @return UserTimesheetDay
     */
    public function setDayStartTime(?string $dayStartTime): UserTimesheetDay
    {
        $this->dayStartTime = $dayStartTime;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDayEndTime(): ?string
    {
        return $this->dayEndTime;
    }

    /**
     * @param string|null $dayEndTime
     *
     * @return UserTimesheetDay
     */
    public function setDayEndTime(?string $dayEndTime): UserTimesheetDay
    {
        $this->dayEndTime = $dayEndTime;

        return $this;
    }

    /**
     * @return float
     */
    public function getWorkingTime(): float
    {
        return $this->workingTime;
    }

    /**
     * @param float $workingTime
     *
     * @return UserTimesheetDay
     */
    public function setWorkingTime($workingTime): UserTimesheetDay
    {
        $this->workingTime = $workingTime;

        return $this;
    }

    /**
     * @return PresenceType|null
     */
    public function getPresenceType(): ?PresenceType
    {
        return $this->presenceType;
    }

    /**
     * @param PresenceType|null $presenceType
     *
     * @return UserTimesheetDay
     */
    public function setPresenceType(?PresenceType $presenceType): UserTimesheetDay
    {
        $this->presenceType = $presenceType;

        return $this;
    }

    /**
     * @return AbsenceType|null
     */
    public function getAbsenceType(): ?AbsenceType
    {
        return $this->absenceType;
    }

    /**
     * @param AbsenceType|null $absenceType
     *
     * @return UserTimesheetDay
     */
    public function setAbsenceType(?AbsenceType $absenceType): UserTimesheetDay
    {
        $this->absenceType = $absenceType;

        return $this;
    }
}
