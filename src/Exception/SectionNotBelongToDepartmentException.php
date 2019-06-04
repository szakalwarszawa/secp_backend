<?php


namespace App\Exception;


class SectionNotBelongToDepartmentException extends \Exception
{
    protected $message = 'Given Section not belong to user Department.';
}
