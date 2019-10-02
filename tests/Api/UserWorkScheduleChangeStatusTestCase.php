<?php

namespace App\Tests\Api;

class UserWorkScheduleChangeStatusTestCase
{
    /**
     * @var
     */
    private $name;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @var
     */
    private $start;
    /**
     * @var
     */
    private $end;
    /**
     * @var
     */
    private $user;
    /**
     * @var
     */
    private $workSchedule;
    /**
     * @var
     */
    private $baseStatus;
    /**
     * @var
     */
    private $endStatus;
    /**
     * @var array
     */
    private $days;

    private $preformattedDays;

    /**
     * @return mixed
     */
    public function getPreformattedDays()
    {
        return $this->preformattedDays;
    }

    /**
     * @param mixed $preformattedDays
     */
    public function setPreformattedDays($preformattedDays): void
    {
        $this->preformattedDays = $preformattedDays;
    }

    /**
     * UserWorkScheduleChangeStatusTestCase constructor.
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
        $user,
        $workSchedule,
        $baseStatus,
        $endStatus,
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
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param mixed $start
     */
    public function setStart($start): void
    {
        $this->start = $start;
    }

    /**
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param mixed $end
     */
    public function setEnd($end): void
    {
        $this->end = $end;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getWorkSchedule()
    {
        return $this->workSchedule;
    }

    /**
     * @param mixed $workSchedule
     */
    public function setWorkSchedule($workSchedule): void
    {
        $this->workSchedule = $workSchedule;
    }

    /**
     * @return mixed
     */
    public function getBaseStatus()
    {
        return $this->baseStatus;
    }

    /**
     * @param mixed $baseStatus
     */
    public function setBaseStatus($baseStatus): void
    {
        $this->baseStatus = $baseStatus;
    }

    /**
     * @return mixed
     */
    public function getEndStatus()
    {
        return $this->endStatus;
    }

    /**
     * @param mixed $endStatus
     */
    public function setEndStatus($endStatus): void
    {
        $this->endStatus = $endStatus;
    }

    /**
     * @return array
     */
    public function getDays(): array
    {
        return $this->days;
    }

    /**
     * @param array $days
     */
    public function setDays(array $days): void
    {
        $this->days = $days;
    }
}