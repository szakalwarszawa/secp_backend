<?php

namespace App\Tests;

/**
 * Class NotFoundReferencedUserException
 * @package App\Tests
 */
class NotFoundReferencedUserException extends \Exception
{
    protected $message = 'User not found in reference repository.';
}
