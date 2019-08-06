<?php declare(strict_types=1);

namespace App\Ldap\Constants;

/**
 * Class UserAttributes
 */
class UserAttributes
{
    /**
     * @var string
     */
    const POSITION = 'title';

    /**
     * @var string
     */
    const SECTION = 'info';

    /**
     * @var string
     */
    const DEPARTMENT_SHORT = 'description';

    /**
     * @var string
     */
    const DEPARTMENT = 'department';

    /**
     * @var string
     */
    const SAMACCOUNTNAME = 'samaccountname';

    /**
     * @var string
     */
    const SUPERVISOR = 'manager';

    /**
     * Get user attribute keys.
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            'firstname',
            'lastname',
            'mail',
            self::SAMACCOUNTNAME,
            self::DEPARTMENT,
            self::POSITION,
            self::SECTION,
            self::DEPARTMENT_SHORT,
            self::SUPERVISOR,
        ];
    }
}
