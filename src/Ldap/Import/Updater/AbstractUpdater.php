<?php

declare(strict_types=1);

namespace App\Ldap\Import\Updater;

use App\Ldap\Import\Updater\Result\Collector;
use App\Ldap\Import\Updater\Result\Result;

/**
 * Class AbstractUpdater
 */
abstract class AbstractUpdater
{
    /**
     * @var Collector
     */
    protected $resultsCollector;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->resultsCollector = new Collector();
    }

    /**
     * Begins operations chain.
     *
     * @return void
     */
    abstract public function update(): void;

    /**
     * Short to collection `add` method.
     *
     * @param Result $result
     *
     * @return bool
     */
    protected function addResult(Result $result): bool
    {
        $this
            ->resultsCollector
            ->add($result)
        ;

        return true;
    }

    /**
     * Returns resultsCollector
     *
     * @return Collector
     */
    public function getResultsCollector(): Collector
    {
        return $this->resultsCollector;
    }

    /**
     * Get count as string.
     *
     * @return string
     */
    public function getCountAsString(): string
    {
        return sprintf(
            'Successful updates: %d, Failed updates: %d',
            $this->getSuccessfulCount(),
            $this->getFailedCount()
        );
    }
}
