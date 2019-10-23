<?php

declare(strict_types=1);

namespace App\Utils\ORM;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use Doctrine\Common\Annotations\AnnotationException;
use App\Annotations\AnnotatedLogEntity;
use App\Entity\Types\LogEntityInterface;
use InvalidArgumentException;

/**
 * Class EntityLogAnnotationReader
 * AnnotatedLogEntity annotation reader.
 */
class EntityLogAnnotationReader
{
    /**
     * Supported annotation class.
     *
     * @return string
     */
    private static function supportsAnnnotation(): string
    {
        return AnnotatedLogEntity::class;
    }

    /**
     * Get log entity form base entity class.
     * It must be defined in `AnnotatedLogEntity` annotation as `logClass`.
     * Also acts as a validator.
     *
     * @param string $baseClassName
     *
     * @throws AnnotationException when base class does not contain requried annotation
     * @throws InvalidArgumentException when class defined in logClass is not instance of LogEntityInterface
     *
     * @return LogEntityInterface
     */
    public static function getEntityLogClassInstance(string $baseClassName): LogEntityInterface
    {
        $reflectionClass = new ReflectionClass($baseClassName);
        $annotationReader = new AnnotationReader();
        $logAnnotation = $annotationReader->getClassAnnotation($reflectionClass, self::supportsAnnnotation());

        if (!$logAnnotation) {
            throw new AnnotationException(sprintf(
                'Class %s does not contain %s annotation.',
                $baseClassName,
                self::supportsAnnnotation()
            ));
        }

        if (!new $logAnnotation->logClass instanceof LogEntityInterface) {
            throw new InvalidArgumentException(sprintf(
                'Instance of %s class expected.',
                LogEntityInterface::class
            ));
        }

        return new $logAnnotation->logClass;
    }

    /**
     * Get supported annotation options with properties names.
     * It calls AnnotatedLogEntity method `validateOptions`.
     *
     * @param string $baseClassName
     *
     * @return array
     */
    public static function getPropertiesToLog(string $baseClassName): array
    {
        $reflectionClass = new ReflectionClass($baseClassName);
        $annotationReader = new AnnotationReader();

        self::getEntityLogClassInstance($baseClassName);

        $classProperties = $reflectionClass->getProperties();
        $propertiesToLog = [];
        foreach ($classProperties as $property) {
            $annotationData = $annotationReader->getPropertyAnnotation($property, self::supportsAnnnotation());
            if ($annotationData) {
                $annotationData->validateOptions();
                $propertiesToLog[$property->name] = $annotationData->options;
            }
        }

        return $propertiesToLog;
    }
}
