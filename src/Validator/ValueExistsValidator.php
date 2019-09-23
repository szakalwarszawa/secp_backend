<?php

declare(strict_types=1);

namespace App\Validator;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Class ValueExistsValidator
 */
class ValueExistsValidator extends ConstraintValidator
{
    /**
     * @var string
     */
    private const RESULT_VALID = 'valid';

    /**
     * @var string
     */
    private const RESULT_INVALID = 'invalid';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Supported data types of this validator.
     *
     * @var array
     */
    private $supportedTypes = [
        'array',
        'string',
        'int',
    ];

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Is incomingValue supported type of data.
     *
     * @param mixed $incomingValue
     *
     * @return bool
     */
    private function supports($incomingValue): bool
    {
        $type = gettype($incomingValue);

        return in_array($type, $this->supportedTypes, true);
    }

    /**
     * Validate persisted value.
     * Value must be present in entity class defined in constraint property.
     *
     * @param mixed $incomingValue
     * @param Constraint $constraint
     *
     * @return void
     */
    public function validate($incomingValue, Constraint $constraint): void
    {
        if (!$this->supports($incomingValue)) {
            return;
        }

        $result = self::RESULT_VALID;
        $existingValues = $this->getEntityValuesAsArray($constraint->entity, $constraint->searchField);

        if (empty($existingValues)) {
            return;
        }

        if (!is_array($incomingValue)) {
            $incomingValue = [$incomingValue];
        }

        $correctElements = array_intersect($existingValues, $incomingValue);
        $incorrectElements = array_diff($incomingValue, $correctElements);

        if ($incorrectElements) {
            $result = self::RESULT_INVALID;
        }

        if (self::RESULT_INVALID === $result) {
            $this
                ->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ value }}', implode(', ', $incorrectElements))
                ->setParameter('{{ class }}', $constraint->entity)
                ->addViolation()
            ;
        }
    }

    /**
     * Returns entity collection with just a specified field.
     *
     * @param string $classPath
     * @param string $searchField
     *
     * @return array
     */
    private function getEntityValuesAsArray(string $classPath, string $searchField): array
    {
        if ($this->hasField($classPath, $searchField)) {
            $query = $this
                ->entityManager
                ->getRepository($classPath)
                ->createQueryBuilder('c')
                ->select(sprintf('c.%s', $searchField))
                ->getQuery();
            ;

            $result = $query->getArrayResult();

            if (empty($result)) {
                return [];
            }

            return array_map(function ($element) use ($searchField) {
                return $element[$searchField];
            }, $result);
        }

        return [];
    }

    /**
     * Check if this entity has given field.
     *
     * @param string $className
     * @param string $fieldName
     *
     * @return bool
     */
    private function hasField(string $className, string $fieldName): bool
    {
        $metadata = $this->getEntityMetadata($className);

        return in_array($fieldName, $metadata->getFieldNames(), true);
    }

    /**
     * Get entity metadata.
     *
     * @param string $className
     * @param bool $throwException
     *
     * @throws InvalidArgumentException when given className is not an entity class.
     *
     * @return null|ClassMetadata
     */
    private function getEntityMetadata(string $className, bool $throwException = false): ?ClassMetadata
    {
        try {
            return $this
                ->entityManager
                ->getMetadataFactory()
                ->getMetadataFor($className)
            ;
        } catch (MappingException $exception) {
            if (!$throwException) {
                return null;
            }

            throw new InvalidArgumentException(
                sprintf('Class %s is not entity.', $className)
            );
        }
    }
}
