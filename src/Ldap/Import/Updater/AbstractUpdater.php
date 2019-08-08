<?php declare(strict_types=1);

namespace App\Ldap\Import\Updater;

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
