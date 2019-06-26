<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

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
class UserTimesheetDay
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserTimesheet", inversedBy="userTimesheetDays")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userTimesheet;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserWorkScheduleDay", inversedBy="userTimesheetDay", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $userWorkScheduleDay;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $dayStartTime;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $dayEndTime;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2)
     */
    private $workingTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\PresenceType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $presenceType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AbsenceType")
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
     * @return UserTimesheetDay
     */
    public function setUserTimesheet(?UserTimesheet $userTimesheet): self
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
     * @return UserTimesheetDay
     */
    public function setUserWorkScheduleDay(UserWorkScheduleDay $userWorkScheduleDay): self
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
     * @return UserTimesheetDay
     */
    public function setDayStartTime(?string $dayStartTime): self
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
     * @return UserTimesheetDay
     */
    public function setDayEndTime(?string $dayEndTime): self
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
     * @return UserTimesheetDay
     */
    public function setWorkingTime($workingTime): self
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
     * @return UserTimesheetDay
     */
    public function setPresenceType(?PresenceType $presenceType): self
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
     * @return UserTimesheetDay
     */
    public function setAbsenceType(?AbsenceType $absenceType): self
    {
        $this->absenceType = $absenceType;

        return $this;
    }
}
