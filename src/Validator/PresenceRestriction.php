<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PresenceRestriction extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Unable to add timesheet day due to edit/add restriction.';

    /**
     * Define as class validator.
     *
     * @return string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
