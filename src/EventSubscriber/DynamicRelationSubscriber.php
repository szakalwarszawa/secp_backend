<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Types\LogEntityInterface;
use App\Entity\Types\LoggableEntityInterface;
use App\Utils\ORM\EntityLogAnnotationReader;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use ReflectionClass;
use ReflectionException;

/**
 * Class DynamicRelationSubscriber
 */
class DynamicRelationSubscriber
{
    /**
     * @var string
     */
    private const LOGS_FIELD = 'logs';

    /**
     * @var string
     */
    private const PARENT_FIELD = 'parent';

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

        if (in_array(LoggableEntityInterface::class, $reflectionClass->getInterfaceNames(), true)) {
            $this->mapLoggableEntity($metadata, $reflectionClass);
        }

        if (in_array(LogEntityInterface::class, $reflectionClass->getInterfaceNames(), true)) {
            $this->mapLogEntity($metadata, $reflectionClass);
        }
    }

    /**
     * Map OneToMany log relation.
     *
     * @param ClassMetadata $classMetadata
     * @param ReflectionClass $reflectionClass
     *
     * @return void
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function mapLoggableEntity(ClassMetadata $classMetadata, ReflectionClass $reflectionClass): void
    {
        $logEntityClass = EntityLogAnnotationReader::getEntityLogClassInstance($reflectionClass->getName());

        $classMetadata->mapOneToMany([
            'fieldName' => self::LOGS_FIELD,
            'targetEntity' => get_class($logEntityClass),
            'mappedBy' => self::PARENT_FIELD,
            'orphanRemoval' => true,
        ]);
    }

    /**
     * Map ManyToOne log relation.
     *
     * @param ClassMetadata $classMetadata
     * @param ReflectionClass $reflectionClass
     *
     * @return void
     * @throws AnnotationException
     * @throws ReflectionException
     */
    private function mapLogEntity(ClassMetadata $classMetadata, ReflectionClass $reflectionClass): void
    {
        $logParentClass = EntityLogAnnotationReader::getEntityLogParentInstance($reflectionClass->getName());

        $classMetadata->mapManyToOne([
           'fieldName' => self::PARENT_FIELD,
           'targetEntity' => get_class($logParentClass),
           'inversedBy' => self::LOGS_FIELD,
           'joinColumn' => [
               'name' => 'parent_id',
               'referencedColumnName' => 'id',
               'nullable' => false,
           ]
       ]);
    }
}
