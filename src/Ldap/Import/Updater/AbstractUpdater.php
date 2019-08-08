<?php declare(strict_types=1);

namespace App\Ldap\Import\Updater;

use App\Ldap\Import\Updater\Result\Collector;
use App\Ldap\Import\Updater\Result\Result;
use App\Ldap\Import\Updater\Result\Types;

/**
 * Class AbstractUpdater
 */
abstract class AbstractUpdater
{
    /**
     * @var int
     */
    protected $successfulUpdates = 0;

    /**
     * @var int
     */
    protected $failedUpdates = 0;

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
     * Increment successfulUpdates
     *
     * @return void
     */
    protected function countSuccess(): void
    {
        $this->successfulUpdates++;
    }

    /**
     * Increment failedUpdates
     *
     * @return void
     */
    protected function countFail(): void
    {
        $this->failedUpdates++;
    }

    protected function addResult(Result $result)
    {
        if (Types::FAIL === $result->getType()) {
            $this->countFail();
        }

        if (Types::SUCCESS === $result->getType()) {
            $this->countSuccess();
        }

        $this
            ->resultsCollector
            ->add($result)
        ;
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
     * Get successful updates count.
     *
     * @return int
     */
    public function getSuccessfulCount(): int
    {
        return $this->successfulUpdates;
    }

    /**
     * Get failed updates count.
     *
     * @return int
     */
    public function getFailedCount(): int
    {
        return $this->failedUpdates;
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
