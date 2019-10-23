<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entity\Types\LoggableEntityInterface;
use App\Entity\Types\LogEntityInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Class LogAccessorTrait
 */
trait LogAccessorTrait
{
    /**
     * {@inheritDoc}
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    /**
     * {@inheritDoc}
     */
    public function addLog(LogEntityInterface $log): LoggableEntityInterface
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setParent($this);
        }

        return $this;
    }

    /**
     * @param UserLog $log
     *
     * @return User
     */
    public function removeLog(LogEntityInterface $log): LoggableEntityInterface
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            if ($log->getParent() === $this) {
                $log->setParent(null);
            }
        }

        return $this;
    }
}
