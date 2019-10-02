<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use App\Controller\UserActiveWorkScheduleAction;
use App\Entity\Utils\UserAware;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="`user_work_schedule_days`",
 *     indexes={
 *          @ORM\Index(name="idx_user_work_schedule_days_user_work_schedule_id", columns={"user_work_schedule_id"}),
 *          @ORM\Index(name="idx_user_work_schedule_days_working_day", columns={"working_day"})
 *     },
 *     uniqueConstraints={
 *         @UniqueConstraint(
 *             name="idx_user_work_schedule_days_user_work_user_timesheet_day_id",
 *             columns={"user_timesheet_day_id"}
 *         )
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserWorkScheduleDayRepository")
 * @UserAware(
 *     troughReferenceTable="user_work_schedules",
 *     troughForeignKey="user_work_schedule_id",
 *     troughReferenceId="id",
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
 *                  "groups"={
 *                      "get"
 *                  }
 *              }
 *          },
 *          "get-active-work-schedule"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "method"="GET",
 *              "path"="/user_work_schedule_days/own/active/{dateFrom}/{dateTo}",
 *              "requirements"={"dateFrom"="\d{4}-\d{2}-\d{2}", "dateTo"="\d{4}-\d{2}-\d{2}"},
 *              "controller"=UserActiveWorkScheduleAction::class,
 *              "normalization_context"={
 *                  "groups"={
 *                      "get"
 *                  }
 *              }
 *          },
 *      },
 *      normalizationContext={
 *          "groups"={
 *              "get"
 *          }
 *      }
 * )
 * @ApiFilter(
 *     RangeFilter::class,
 *     properties={
 *         "dayDefinition.id"
 *     }
 * )
 */
class UserWorkScheduleDay
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get", "get-user-timesheet-day-with-user_work_schedule_day"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserWorkSchedule", inversedBy="userWorkScheduleDays")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get"})
     */
    private $userWorkSchedule;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DayDefinition")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get"})
     */
    private $dayDefinition;

    /**
     * @ORM\Column(
     *     type="boolean",
     *     options={"default"=true}
     * )
     * @Groups({"get"})
     */
    private $deleted;

    /**
     * @return bool|int
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     * @return UserWorkScheduleDay
     */
    public function setDeleted(bool $deleted): UserWorkScheduleDay
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups({"get"})
     */
    private $workingDay;

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
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\UserTimesheetDay",
     *     mappedBy="userWorkScheduleDay",
     *     cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(nullable=true)
     */
    private $userTimesheetDay;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return UserWorkSchedule|null
     */
    public function getUserWorkSchedule(): ?UserWorkSchedule
    {
        return $this->userWorkSchedule;
    }

    /**
     * @param UserWorkSchedule|null $userWorkSchedule
     *
     * @return UserWorkScheduleDay
     */
    public function setUserWorkSchedule(?UserWorkSchedule $userWorkSchedule): UserWorkScheduleDay
    {
        $this->userWorkSchedule = $userWorkSchedule;

        return $this;
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
     * @return UserWorkScheduleDay
     */
    public function setDayDefinition(?DayDefinition $dayDefinition): UserWorkScheduleDay
    {
        $this->dayDefinition = $dayDefinition;

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
     * @return UserWorkScheduleDay
     */
    public function setWorkingDay(bool $workingDay): UserWorkScheduleDay
    {
        $this->workingDay = $workingDay;

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
     * @param string|null $dayEndTimeFrom
     *
     * @return UserWorkScheduleDay
     */
    public function setDayEndTimeFrom(?string $dayEndTimeFrom): UserWorkScheduleDay
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
     * @param string|null $dayEndTimeTo
     *
     * @return UserWorkScheduleDay
     */
    public function setDayEndTimeTo(?string $dayEndTimeTo): UserWorkScheduleDay
    {
        $this->dayEndTimeTo = $dayEndTimeTo;

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
     * @return UserWorkScheduleDay
     */
    public function setDayStartTimeFrom(string $dayStartTimeFrom): UserWorkScheduleDay
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
     * @return UserWorkScheduleDay
     */
    public function setDayStartTimeTo(string $dayStartTimeTo): UserWorkScheduleDay
    {
        $this->dayStartTimeTo = $dayStartTimeTo;

        return $this;
    }

    /**
     * @return float
     */
    public function getDailyWorkingTime(): float
    {
        return $this->dailyWorkingTime;
    }

    /**
     * @param float $dailyWorkingTime
     *
     * @return UserWorkScheduleDay
     */
    public function setDailyWorkingTime(float $dailyWorkingTime): UserWorkScheduleDay
    {
        $this->dailyWorkingTime = $dailyWorkingTime;

        return $this;
    }

    /**
     * @return UserTimesheetDay|null
     */
    public function getUserTimesheetDay(): ?UserTimesheetDay
    {
        return $this->userTimesheetDay;
    }

    /**
     * @param UserTimesheetDay $userTimesheetDay
     *
     * @return UserWorkScheduleDay
     */
    public function setUserTimesheetDay(UserTimesheetDay $userTimesheetDay): UserWorkScheduleDay
    {
        $this->userTimesheetDay = $userTimesheetDay;

        // set the owning side of the relation if necessary
        if ($this !== $userTimesheetDay->getUserWorkScheduleDay()) {
            $userTimesheetDay->setUserWorkScheduleDay($this);
        }

        return $this;
    }
}
