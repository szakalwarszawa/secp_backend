<?php

declare(strict_types=1);

namespace App\Ldap\Import\Updater\Result;

/**
 * Class Actions
 */
class Actions
{
    /**
     * @var string
     */
    public const CREATE = 'create';

    /**
     * @var string
     */
    public const UPDATE = 'update';

    /**
     * @var string
     */
    public const IGNORE = 'ignore';
}
