<?php

declare(strict_types=1);

namespace App\Utils\ORM;

use App\Annotations\ParentEntity;
use App\Entity\Types\LoggableEntityInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use Doctrine\Common\Annotations\AnnotationException;
use App\Annotations\AnnotatedLogEntity;
use App\Entity\Types\LogEntityInterface;
use InvalidArgumentException;
use ReflectionException;

/**
 * Class EntityLogAnnotationReader
 * AnnotatedLogEntity annotation reader.
 */
class EntityLogAnnotationReader
{
    /**
     * @var string
     */
    private static $loggableEntityAnnotation = AnnotatedLogEntity::class;

    /**
     * @var string
     */
    private static $logEntityAnnotation = ParentEntity::class;

    /**
     * @param string $logEntityClassName
     *
     * @return LoggableEntityInterface
     * @throws AnnotationException
     * @throws ReflectionException
     */
    public static function getEntityLogParentInstance(string $logEntityClassName): LoggableEntityInterface
    {
        $reflectionClass = new ReflectionClass($logEntityClassName);
        $annotationReader = new AnnotationReader();
        $logAnnotation = $annotationReader->getClassAnnotation($reflectionClass, self::$logEntityAnnotation);

        if (!$logAnnotation) {
            throw new AnnotationException(sprintf(
                'Class %s does not contain %s annotation.',
                $logEntityClassName,
                self::$logEntityAnnotation
            ));
        }

        if (!new $logAnnotation->className() instanceof LoggableEntityInterface) {
            throw new InvalidArgumentException(sprintf(
                'Instance of %s class expected.',
                LoggableEntityInterface::class
            ));
        }

        return new $logAnnotation->className();
    }

    /**
     * Get log entity form base entity class.
     * It must be defined in `AnnotatedLogEntity` annotation as `logClass`.
     * Also acts as a validator.
     *
     * @param string $baseClassName
     *
     * @return LogEntityInterface
     *
     * @throws AnnotationException
     * @throws ReflectionException
     */
    public static function getEntityLogClassInstance(string $baseClassName): LogEntityInterface
    {
        $reflectionClass = new ReflectionClass($baseClassName);
        $annotationReader = new AnnotationReader();
        $logAnnotation = $annotationReader->getClassAnnotation($reflectionClass, self::$loggableEntityAnnotation);

        if (!$logAnnotation) {
            throw new AnnotationException(sprintf(
                'Class %s does not contain %s annotation.',
                $baseClassName,
                self::$loggableEntityAnnotation
            ));
        }


        if (!new $logAnnotation->logClass() instanceof LogEntityInterface) {
            throw new InvalidArgumentException(sprintf(
                'Instance of %s class expected.',
                LogEntityInterface::class
            ));
        }

        return new $logAnnotation->logClass();
    }

    /**
     * Get supported annotation options with properties names.
     * It calls AnnotatedLogEntity method `validateOptions`.
     *
     * @param string $baseClassName
     *
     * @return array
     *
     * @throws AnnotationException
     * @throws ReflectionException
     */
    public static function getPropertiesToLog(string $baseClassName): array
    {
        $reflectionClass = new ReflectionClass($baseClassName);
        $annotationReader = new AnnotationReader();

        self::getEntityLogClassInstance($baseClassName);

        $classProperties = $reflectionClass->getProperties();
        $propertiesToLog = [];
        foreach ($classProperties as $property) {
            $annotationData = $annotationReader->getPropertyAnnotation($property, self::$loggableEntityAnnotation);
            if ($annotationData) {
                $annotationData->validateOptions();
                $propertiesToLog[$property->name] = $annotationData->options;
            }
        }

        return $propertiesToLog;
    }
}
