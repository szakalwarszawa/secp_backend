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
     * @var bool
     */
    private $joinFailures = false;

    /**
     * Get results marked as success.
     *
     * Alias to getByType(Types::SUCCESS)
     *
     * @return array
     */
    public function getSucceed(): array
    {
        return $this->getByType(Types::SUCCESS);
    }

    /**
     * Get results marked as fail.
     *
     * Alias to getByType(Types::FAIL)
     *
     * @return array
     */
    public function getFailed(): array
    {
        return $this->getByType(Types::FAIL);
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
            function ($element, $key) use ($type) {
                return $element instanceof Result && $type === $element->getType();
            },
            ARRAY_FILTER_USE_BOTH
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

        if ($this->joinFailures) {
            return [
                Types::SUCCESS => isset($sortedObjects[Types::SUCCESS]) ? count($sortedObjects[Types::SUCCESS]) : 0,
                Types::FAIL => $sortedObjects[Types::FAIL] ?? 0,
            ];
        }

        return [
            Types::SUCCESS => isset($sortedObjects[Types::SUCCESS]) ? count($sortedObjects[Types::SUCCESS]) : 0,
            Types::FAIL => isset($sortedObjects[Types::FAIL]) ? count($sortedObjects[Types::FAIL]) : 0,
        ];
    }

    /**
     * Force join failures to return regardless of the method.
     *
     * @return Collector
     */
    public function forceJoinFailures(): Collector
    {
        $this->joinFailures = true;

        return $this;
    }
}
