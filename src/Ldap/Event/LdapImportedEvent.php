<?php

declare(strict_types=1);

namespace App\Ldap\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class LdapImportedEvent
 */
class LdapImportedEvent extends Event
{
    /**
     * @var string
     */
    public const NAME = 'ldap.imported';

    /**
     * @var array
     */
    protected $results;

    /**
     * @param array $results
     */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    /**
     * Get results
     *
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
