<?php

declare(strict_types=1);

namespace App\Entity\Types;

use DateTimeInterface;
use App\Entity\User;

/**
 * Entities that implement this interface are considered as log.
 */
interface LogEntityInterface
{
     /**
     * @return null|User
     */
    public function getOwner(): ?User;

    /**
     * @param null|User $owner
     */
    public function setOwner(?User $owner);

    /**
     * @return DateTimeInterface
     */
    public function getLogDate(): DateTimeInterface;

    /**
     * @param DateTimeInterface $logDate
     */
    public function setLogDate(DateTimeInterface $logDate);

    /**
     * @return null|string
     */
    public function getNotice(): ?string;

    /**
     * @param string $notice
     */
    public function setNotice(string $notice);

    /**
     * @return string|null
     *
     * @deprecated
     */
    //public function getTrigger(): ?string;

    /**
     *
     * @param string|null $triggerName
     *
     * @deprecated
     */
    //public function setTrigger(?string $triggerName);

    /**
     * @return string|null
     *
     */
    //public function getElementTrigger(): ?string;

    /**
     *
     * @param string|null $triggerName
     */
    //public function setElementTrigger(?string $triggerName);

    //public function getParent();
    //private function setParent();
}
