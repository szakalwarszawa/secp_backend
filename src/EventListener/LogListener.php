<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Types\LoggableEntityInterface;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use App\Utils\ORM\EntityChangeSetLogBuilder;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * Class LogListener
 */
class LogListener
{
    /**
     * @var EntityChangeSetLogBuilder
     */
    private $entityChangeSetLogBuilder;

    /**
     * @var array
     */
    private $logPersistSchedule = [];

    /**
     * @param EntityChangeSetLogBuilder $entityChangeSetLogBuilder
     */
    public function __construct(EntityChangeSetLogBuilder $entityChangeSetLogBuilder)
    {
        $this->entityChangeSetLogBuilder = $entityChangeSetLogBuilder;
    }

    /**
     * Store logs to persist in postFlush.
     *
     * @param OnFlushEventArgs $args
     *
     * @throws AnnotationException
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof LoggableEntityInterface) {
                $logs = $this
                    ->entityChangeSetLogBuilder
                    ->build($entity)
                ;

                if ($logs) {
                    $this->logPersistSchedule[] = $logs;
                }
            }
        }
    }

    /**
     * Persist and flush scheduled logs.
     *
     * @param PostFlushEventArgs $args
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @return void
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        $entityManager = $args->getEntityManager();
        if (!empty($this->logPersistSchedule)) {
            foreach ($this->logPersistSchedule as $singleEntityLogs) {
                foreach ($singleEntityLogs as $log) {
                    $entityManager->persist($log);
                }
            }

            $this->logPersistSchedule = [];
            $entityManager->flush();
        }
    }
}
