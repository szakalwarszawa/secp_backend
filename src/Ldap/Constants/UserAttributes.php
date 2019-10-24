<?php

declare(strict_types=1);

namespace App\Ldap\Constants;

/**
 * Class UserAttributes
 */
class UserAttributes
{
    /**
     * @var string
     */
    public const FIRST_NAME = 'firstname';

    /**
     * @var string
     */
    public const LAST_NAME = 'lastname';

    /**
     * @var string
     */
    public const MAIL = 'mail';

    /**
     * @var string
     */
    public const POSITION = 'title';

    /**
     * @var string
     */
    public const SECTION = 'info';

    /**
     * @var string
     */
    public const DEPARTMENT_SHORT = 'description';

    /**
     * @var string
     */
    public const DEPARTMENT = 'department';

    /**
     * @var string
     */
    public const SAMACCOUNTNAME = 'samaccountname';

    /**
     * @var string
     */
    public const SUPERVISOR = 'manager';

    /**
     * Get user attribute keys.
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            self::FIRST_NAME,
            self::LAST_NAME,
            self::MAIL,
            self::SAMACCOUNTNAME,
            self::DEPARTMENT,
            self::POSITION,
            self::SECTION,
            self::DEPARTMENT_SHORT,
            self::SUPERVISOR,
        ];
    }
}
