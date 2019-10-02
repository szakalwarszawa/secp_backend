<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

/**
 * Class IncorrectStatusChangeException
 */
class IncorrectStatusChangeException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'Brak uprawnień do ustawienia wybranego statusu.';
}
