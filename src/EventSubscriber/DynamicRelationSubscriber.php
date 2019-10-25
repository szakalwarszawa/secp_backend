<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Types\LoggableEntityInterface;
use App\Utils\ORM\EntityLogAnnotationReader;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use ReflectionClass;
use ReflectionException;

/**
 * Class DynamicRelationSubscriber
 */
class DynamicRelationSubscriber
{
    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     *
     * @return void
     * @throws AnnotationException
     * @throws ReflectionException
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $metadata = $eventArgs->getClassMetadata();
        $reflectionClass = new ReflectionClass($metadata->getName());
        if (!in_array(LoggableEntityInterface::class, $reflectionClass->getInterfaceNames(), true)) {
            return;
        }

        $logEntityClass = EntityLogAnnotationReader::getEntityLogClassInstance($reflectionClass->getName());

        $metadata->mapOneToMany([
            'fieldName' => 'logs',
            'targetEntity'=> get_class($logEntityClass),
            'mappedBy' => 'parent',
            'orphanRemoval' => true,
        ]);
    }
}
