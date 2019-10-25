<?php

declare(strict_types=1);

namespace App\Traits;

use App\Entity\Types\LoggableEntityInterface;
use App\Entity\Types\LogEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiSubresource;

/**
 * Trait LogAccessorTrait
 */
trait LogAccessorTrait
{
    /**
     * @var ArrayCollection
     *
     * @ApiSubresource
     */
    private $logs;

    /**
     * LogAccessorTrait constructor.
     */
    public function __construct()
    {
        $this->logs = new ArrayCollection();
    }

    /**
     * @return Collection
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    /**
     * @param LogEntityInterface $log
     *
     * @return LoggableEntityInterface
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
     * @param LogEntityInterface $log
     *
     * @return LoggableEntityInterface
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
