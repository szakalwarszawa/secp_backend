<?php

declare(strict_types=1);

namespace App\Exception;

use Doctrine\ORM\EntityNotFoundException;
use Throwable;

/**
 * Class SpecialObjectNotFoundException
 */
class SpecialObjectNotFoundException extends EntityNotFoundException
{
    /**
     * @var string
     */
    private $messageFormat = 'Special object with provided key (%s) was not found in database.';

    /**
     * {@inheritDoc}
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = sprintf(
            $this->messageFormat,
            $message
        );

        parent::__construct($message, $code, $previous);
    }
}
