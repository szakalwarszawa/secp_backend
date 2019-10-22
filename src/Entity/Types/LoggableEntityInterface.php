<?php

declare(strict_types=1);

namespace App\Entity\Types;

/**
 * Entities that implement this interface are considered as loggable.
 * These entities must have AnnotatedLogEntity(logClass="") annotation
 * and at least one `AnnotatedLogEntity` property to log.
 */
interface LoggableEntityInterface
{
}
