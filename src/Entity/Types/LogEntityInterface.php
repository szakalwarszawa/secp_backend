<?php

declare(strict_types=1);

namespace App\Entity\Types;

use DateTimeInterface;
use App\Entity\Types\LoggableEntityInterface;
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
     * @todo Na czas migracji na nowy sposób logowania zmian poniższe elementy
     * muszą zostać zakomentowane. Po przeniesieniu wszystkich logów na nowy mechanizm
     * zostaną zaimplementowane.
     *
     */

    /**
     * @return string|null
     *
     * @deprecated
     */
    //public function getTrigger(): ?string;

    /**
     * @param string|null $triggerName
     *
     * @deprecated
     */
    //public function setTrigger(?string $triggerName);

    /**
     * @return string|null
     * @todo
     */
    //public function getElementTrigger(): ?string;

    /**
     *
     * @param string|null $triggerName
     * @todo
     */
    //public function setElementTrigger(?string $triggerName);

    /**
     * @todo
     *
     * @return LoggableEntityInterface
     */
    //public function getParent();

    /**
     * @todo
     *
     * @param LoggableEntityInterface
     *
     * @return void
     */
    //private function setParent(LoggableEntityInterface $parent);
}
