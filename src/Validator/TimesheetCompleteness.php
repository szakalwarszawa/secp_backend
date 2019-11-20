<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TimesheetCompleteness extends Constraint
{
    /*
     * @var string
     */
    public $message = 'Timesheet is not complete. Days missing: {{value}}.';
}
