<?php

declare(strict_types=1);

namespace App\Utils;

use InvalidArgumentException;
use ReflectionClass;

/**
 * Class ConstantsUtil
 */
class ConstantsUtil
{
    /**
     * @var null|string
     */
    private static $subjectClassName;

    /**
     * @param null|string $subjectClassName
     */
    public function __construct(?string $subjectClassName = null)
    {
        self::$subjectClassName = $subjectClassName;
    }

    /**
     * Get all constants.
     *
     * @param null|string $subjectClassName
     * @param bool $includeKeys
     *
     * @return array
     */
    public static function getAllConstants(?string $subjectClassName = null, bool $includeKeys = false): array
    {
        $subjectClassName = self::resolveClassName($subjectClassName);

        $thisClass = new ReflectionClass($subjectClassName);
        $constants = $thisClass->getConstants();

        $constantsValues = [];
        if (!$includeKeys) {
            foreach ($constants as $constantValue) {
                $constantsValues[] = $constantValue;
            }

            return $constantsValues;
        }

        return $constants;
    }

    /**
     * Returns given value if exists in given class constants.
     *
     * @param string $value
     * @param null|string $className
     *
     * @throws InvalidArgumentException when subject class does not contain constant with given value
     *
     * @return null|string
     */
    public static function constValue(?string $value = null, ?string $subjectClassName = null): ?string
    {
        if (!$value) {
            return null;
        }

        $subjectClassName = self::resolveClassName($subjectClassName);

        $reflectionClass = new ReflectionClass($subjectClassName);
        $constants = array_flip($reflectionClass->getConstants());
        if (!isset($constants[$value])) {
            throw new InvalidArgumentException(
                sprintf('Class %s does not contain a constant of value %s', $subjectClassName, $value)
            );
        }

        return $value;
    }


    /**
     * Returns value of constant by key.
     *
     * @param string $key
     *
     * @return null|int
     */
    public static function keyToValue(string $key, ?string $subjectClassName = null): ?int
    {
        $constants = self::getAllConstants($subjectClassName, true);

        return $constants[$key] ?? null;
    }

    /**
     * Returns key of constant by value.
     *
     * @param int $value
     * @param null|string $subjectClassName
     *
     * @return null|string
     */
    public static function valueToKey(int $value, ?string $subjectClassName = null): ?string
    {
        $constants = array_flip(self::getAllConstants($subjectClassName, true));

        return $constants[$value] ?? null;
    }

    /**
     * Returns constants keys as string.
     *
     * @param null|string $subjectClassName
     * @param null|string $subjectClassName
     *
     * @return string
     */
    public static function stringify(?string $subjectClassName = null): string
    {
        $keys = array_keys(self::getAllConstants($subjectClassName, true));

        return implode(', ', $keys);
    }

    /**
     * @param null|string $className
     *
     * @throws InvalidArgumentException when class name is not passed
     *
     * @return string
     */
    private static function resolveClassName(?string $className = null): string
    {
        if (!$className && !self::$subjectClassName) {
            throw new InvalidArgumentException('Class name not passed.');
        }

        if (!$className) {
            $className = self::$subjectClassName;
        }

        return $className;
    }
}
