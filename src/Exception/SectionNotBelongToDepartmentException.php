<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Class SectionNotBelongToDepartmentException
 * @package App\Exception
 */
class SectionNotBelongToDepartmentException extends \Exception
{
    protected $message = 'Given Section not belong to user Department.';
}
