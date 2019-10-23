<?php

declare(strict_types=1);

namespace App\Entity\Types;

use Doctrine\Common\Collections\Collection;
use App\Entity\Types\LogEntityInterface;

/**
 * Entities that implement this interface are considered as loggable.
 * These entities must have AnnotatedLogEntity(logClass="") annotation
 * and at least one `AnnotatedLogEntity` property to log.
 */
interface LoggableEntityInterface
{
    /**
     * Get logs
     *
     * @return Collection
     */
    public function getLogs(): Collection;

    /**
     * Add log to collection
     *
     * @param LogEntityInterface $log
     *
     * @return LoggableEntityInterface
     */
    public function addLog(LogEntityInterface $log): LoggableEntityInterface;

    /**
     * Remove log from collection
     *
     * @param LogEntityInterface $log
     *
     * @return LoggableEntityInterface
     */
    public function removeLog(LogEntityInterface $log): LoggableEntityInterface;
}
