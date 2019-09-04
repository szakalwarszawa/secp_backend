<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UserRole extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Jedna lub więcej ról nie jest prawidłowa ("{{ value }}")';
}
