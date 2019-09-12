<?php

declare(strict_types=1);

namespace App\Serializer\GroupsRestrictions;

/**
 * Abstract class AbstractIORestriction
 */
abstract class AbstractIORestriction
{
    /**
     * Returns all class restrictions.
     *
     * It containts array of required roles and name that will be given to the group.
     * ex. [
     *      'role' => ['ROLE_SUPERVISOR'],
     *      'group_name' => 'onlysupervisor:io`
     * ]
     * This means that the 'onlysupervisor:io' @Group will be available
     * in the supported class specified in `supports()` method.
     * ApiPlatform entity property marked with this @Group will be available only
     * for ROLE_SUPERVISOR or another higher in the hierarchy (ex. ROLE_ADMIN).
     *
     * Restriction (class which extends AbstractIORestriction) could be for
     * read or write (input/output) that depends on implemented interface.
     *
     *
     * @return array
     */
    abstract public static function getAll(): array;

    /**
     * Returns class supported by restriction.
     *
     * @return string
     */
    abstract public static function supports(): string;

    /**
     * Append IO suffix to group name.
     * ex. 'hr' (output type) => 'hr:output'
     * (Above example in GroupsRestrictions\Output\UserTimesheetDayGroups)
     *
     * @param string $key
     *
     * @return string
     */
    public function appendSuffix(string $key): string
    {
        return $key . static::SUFFIX;
    }
}
