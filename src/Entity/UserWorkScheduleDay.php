<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
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
class UserWorkScheduleDay
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserWorkSchedule", inversedBy="userWorkScheduleDays")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userWorkSchedule;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DayDefinition")
     * @ORM\JoinColumn(nullable=false)
     */
    private $dayDefinition;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Assert\NotNull()
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
     */
    private $dailyWorkingTime = 8.00;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserTimesheetDay", mappedBy="userWorkScheduleDay", cascade={"persist", "remove"})
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
     * @return UserWorkScheduleDay
     */
    public function setUserWorkSchedule(?UserWorkSchedule $userWorkSchedule): self
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
     * @return UserWorkScheduleDay
     */
    public function setDayDefinition(?DayDefinition $dayDefinition): self
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
     * @return UserWorkScheduleDay
     */
    public function setWorkingDay(bool $workingDay): self
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
     * @return UserWorkScheduleDay
     */
    public function setDayEndTimeFrom(?string $dayEndTimeFrom): self
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
     * @return UserWorkScheduleDay
     */
    public function setDayEndTimeTo(?string $dayEndTimeTo): self
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
     * @return UserWorkScheduleDay
     */
    public function setDayStartTimeFrom(string $dayStartTimeFrom): self
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
     * @return UserWorkScheduleDay
     */
    public function setDayStartTimeTo(string $dayStartTimeTo): self
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
     * @return UserWorkScheduleDay
     */
    public function setDailyWorkingTime(float $dailyWorkingTime): self
    {
        $this->dailyWorkingTime = $dailyWorkingTime;

        return $this;
    }

    public function getUserTimesheetDay(): ?UserTimesheetDay
    {
        return $this->userTimesheetDay;
    }

    public function setUserTimesheetDay(UserTimesheetDay $userTimesheetDay): self
    {
        $this->userTimesheetDay = $userTimesheetDay;

        // set the owning side of the relation if necessary
        if ($this !== $userTimesheetDay->getUserWorkScheduleDay()) {
            $userTimesheetDay->setUserWorkScheduleDay($this);
        }

        return $this;
    }
}
