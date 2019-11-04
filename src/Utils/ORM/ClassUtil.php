<?php

declare(strict_types=1);

namespace App\Utils\ORM;

use Doctrine\Common\Persistence\Proxy;

/**
 * Class ClassUtil
 */
class ClassUtil
{
    /**
     * Gets the real class name of a class name that could be a proxy.
     *
     * @see https://github.com/doctrine/common/issues/867
     *
     * @param string $class
     *
     * @return string
     */
    public static function getRealClass(string $class): string
    {
        $proxyMarker = strrpos($class, '\\' . Proxy::MARKER . '\\');
        if (false === $proxyMarker) {
            return $class;
        }

        return substr($class, $proxyMarker + Proxy::MARKER_LENGTH + 2);
    }
}
