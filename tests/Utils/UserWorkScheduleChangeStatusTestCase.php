<?php

declare(strict_types=1);

namespace App\Tests\Utils;

/**
 * Class UserWorkScheduleChangeStatusTestCase
 */
class UserWorkScheduleChangeStatusTestCase
{
    /**
     * UserWorkScheduleChangeStatusTestCase constructor.
     *
     * @param string $name
     * @param string $start
     * @param string $end
     * @param $user
     * @param $workSchedule
     * @param $baseStatus
     * @param $endStatus
     * @param array $days
     */
    public function __construct(
        string $name,
        string $start,
        string $end,
        string $user,
        string $workSchedule,
        string $baseStatus,
        string $endStatus,
        array $days
    ) {
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->user = $user;
        $this->workSchedule = $workSchedule;
        $this->baseStatus = $baseStatus;
        $this->endStatus = $endStatus;
        $this->days = $days;
    }

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $start;

    /**
     * @var string
     */
    private $end;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $workSchedule;

    /**
     * @var string
     */
    private $baseStatus;

    /**
     * @var string
     */
    private $endStatus;

    /**
     * @var array
     */
    private $days;

    /**
     * @var array
     */
    private $preformattedDays;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getStart(): string
    {
        return $this->start;
    }

    /**
     * @return string
     */
    public function getEnd(): string
    {
        return $this->end;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getWorkSchedule(): string
    {
        return $this->workSchedule;
    }

    /**
     * @return string
     */
    public function getBaseStatus(): string
    {
        return $this->baseStatus;
    }

    /**
     * @return string
     */
    public function getEndStatus(): string
    {
        return $this->endStatus;
    }

    /**
     * @return array
     */
    public function getDays(): array
    {
        return $this->days;
    }

    /**
     * @return array
     */
    public function getPreformattedDays(): array
    {
        return $this->preformattedDays;
    }

    /**
     * @param $name
     *
     * @return UserWorkScheduleChangeStatusTestCase
     */
    public function setName($name): UserWorkScheduleChangeStatusTestCase
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $start
     *
     * @return UserWorkScheduleChangeStatusTestCase
     */
    public function setStart(string $start): UserWorkScheduleChangeStatusTestCase
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @param string $end
     *
     * @return UserWorkScheduleChangeStatusTestCase
     */
    public function setEnd(string $end): UserWorkScheduleChangeStatusTestCase
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @param string $user
     *
     * @return UserWorkScheduleChangeStatusTestCase
     */
    public function setUser(string $user): UserWorkScheduleChangeStatusTestCase
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param string $workSchedule
     *
     * @return UserWorkScheduleChangeStatusTestCase
     */
    public function setWorkSchedule(string $workSchedule): UserWorkScheduleChangeStatusTestCase
    {
        $this->workSchedule = $workSchedule;

        return $this;
    }

    /**
     * @param string $baseStatus
     *
     * @return UserWorkScheduleChangeStatusTestCase
     */
    public function setBaseStatus(string $baseStatus): UserWorkScheduleChangeStatusTestCase
    {
        $this->baseStatus = $baseStatus;

        return $this;
    }

    /**
     * @param string $endStatus
     *
     * @return UserWorkScheduleChangeStatusTestCase
     */
    public function setEndStatus(string $endStatus): UserWorkScheduleChangeStatusTestCase
    {
        $this->endStatus = $endStatus;

        return $this;
    }

    /**
     * @param array $days
     *
     * @return UserWorkScheduleChangeStatusTestCase
     */
    public function setDays(array $days): UserWorkScheduleChangeStatusTestCase
    {
        $this->days = $days;

        return $this;
    }

    /**
     * @param array $preformattedDays
     *
     * @return UserWorkScheduleChangeStatusTestCase
     */
    public function setPreformattedDays(array $preformattedDays): UserWorkScheduleChangeStatusTestCase
    {
        $this->preformattedDays = $preformattedDays;

        return $this;
    }
}
