<?php


namespace App\Tests;


class NotFoundReferencedUserException extends \Exception
{
    protected $message = 'User not found in reference repository.';
}