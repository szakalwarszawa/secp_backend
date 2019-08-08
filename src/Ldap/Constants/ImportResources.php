<?php declare(strict_types=1);

namespace App\Ldap\Constants;

use ReflectionClass;

/**
 * Class ImportResources
 */
class ImportResources
{
    /**
     * @var int
     */
    public const IMPORT_ALL = 1;

    /**
     * @var int
     */
    public const IMPORT_DEPARTMENT_SECTION = 2;

    /**
     * @var int
     */
    public const IMPORT_USERS = 3;

    /**
     * Get all constants.
     *
     * @param bool $includeKeys
     *
     * @return array
     */
    public static function getAll(bool $includeKeys = false): array
    {
        $constantsValues = [];
        $thisClass = new ReflectionClass(__CLASS__);
        $constants = $thisClass->getConstants();

        if (!$includeKeys) {
            foreach ($constants as $constantValue) {
                $constantsValues[] = $constantValue;
            }

            return $constantsValues;
        }

        return $constants;
    }

    /**
     * Returns constants keys as string.
     *
     * @return string
     */
    public static function stringify(): string
    {
        $keys = array_keys(self::getAll(true));

        return implode(', ', $keys);
    }

    /**
     * Returns value of constant by key.
     *
     * @param string $key
     *
     * @return null|int
     */
    public static function keyToValue(string $key): ?int
    {
        $constants = self::getAll(true);

        return $constants[$key] ?? null;
    }

    /**
     * Returns key of constant by value.
     *
     * @param int $value
     *
     * @return null|string
     */
    public static function valueToKey(int $value): ?string
    {
        $constants = array_flip(self::getAll(true));

        return $constants[$value] ?? null;
    }
}
