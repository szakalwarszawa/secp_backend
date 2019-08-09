<?php

namespace App\Ldap\Import\Updater\Result;

use Doctrine\Common\Collections\ArrayCollection;
use App\Utils\ConstantsUtil;
use Symfony\Component\VarDumper\VarDumper;
use App\Ldap\Import\Updater\Result\Result;

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
     * @return Collector
     */
    public function getSucceed()
    {
        return $this->getByType(Types::SUCCESS);
    }

    /**
     * Get results marked as fail.
     *
     * Alias to getByType(Types::FAIL)
     *
     * @return Collector
     */
    public function getFailed()
    {
        return $this->getByType(Types::FAIL);
    }

    /**
     * Returns collector results by type.
     *
     * @param string $type
     *
     * @return Collector
     */
    private function getByType(string $type): Collector
    {
        ConstantsUtil::constCheckValue($type, Types::class);
        $tempCollector = new Collector();
        foreach ($this as $element) {
            if ($element instanceof Result && $type === $element->getType()) {
                $tempCollector->add($element);
            }
        }

        return $tempCollector;
    }

    /**
     * Returns results sorted by type as array.
     *
     * @return array
     */
    public function getSorted(): array
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
        $sortedObjects = $this->getSorted();

        if ($this->joinFailures) {
            return [
                Types::SUCCESS => isset($sortedObjects[Types::SUCCESS]) ? count($sortedObjects[Types::SUCCESS]) : 0,
                Types::FAIL => isset($sortedObjects[Types::FAIL]) ? $sortedObjects[Types::FAIL] : 0,
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
