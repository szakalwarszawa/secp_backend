<?php

namespace App\Ldap\Import\Updater\Result;

use Doctrine\Common\Collections\ArrayCollection;
use App\Utils\ConstantsUtil;

/**
 * Class Collector
 */
class Collector extends ArrayCollection
{
    /**
     * Get results marked as success.
     *
     * Alias to getByType(Types::SUCCESS)
     *
     * @param bool $simpleArray
     *
     * @return array
     */
    public function getSucceed(bool $simpleArray = false): array
    {
        if (!$simpleArray) {
            return $this->getByType(Types::SUCCESS);
        }

        return array_map(function ($element) {
            return '[' . $element->getAction() . '] ' . $element->getTarget();
        }, $this->getByType(Types::SUCCESS));
    }

    /**
     * Get results marked as fail.
     *
     * Alias to getByType(Types::FAIL)
     *
     * @param bool $simpleArray
     *
     * @return array
     */
    public function getFailed(bool $simpleArray = false): array
    {
        if (!$simpleArray) {
            return $this->getByType(Types::FAIL);
        }

        return array_map(function ($element) {
            return $element->getTarget();
        }, $this->getByType(Types::FAIL));
    }

    /**
     * Returns collector results by type.
     *
     * @param string $type
     *
     * @return array
     */
    private function getByType(string $type): array
    {
        ConstantsUtil::constCheckValue($type, Types::class);

        return array_filter(
            $this->toArray(),
            function ($element) use ($type) {
                return $element instanceof Result && $type === $element->getType();
            }
        );
    }

    /**
     * Returns results sorted by type as array.
     *
     * @return array
     */
    public function getGroupByType(): array
    {
        $tempArray = [];
        foreach ($this as $element) {
            if ($element instanceof Result) {
                $tempArray[$element->getType()][] = $element;
            }
        }

        return $tempArray;
    }

    /**
     * Returns counters for types.
     *
     * @return array
     */
    public function getCounters(): array
    {
        $sortedObjects = $this->getGroupByType();

        return [
            Types::SUCCESS => isset($sortedObjects[Types::SUCCESS]) ? count($sortedObjects[Types::SUCCESS]) : 0,
            Types::FAIL => isset($sortedObjects[Types::FAIL]) ? count($sortedObjects[Types::FAIL]) : 0,
        ];
    }
}
