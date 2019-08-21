<?php
declare(strict_types=1);

namespace App\Filter;

use App\Entity\Utils\UserAware;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;
use InvalidArgumentException;

/**
 * Class UserFilter
 * @package App\Filter
 */
class UserFilter extends SQLFilter
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @param ClassMetaData $targetEntity
     * @param string $targetTableAlias
     *
     * @return string
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if (empty($this->reader)) {
            return '';
        }

        $userAware = $this->reader->getClassAnnotation(
            $targetEntity->getReflectionClass(),
            UserAware::class
        );
        /* @var $userAware UserAware */

        if (!$userAware) {
            return '';
        }

        $fieldName = $userAware->userFieldName;

        try {
            $userId = $this->getParameter('id');
        } catch (InvalidArgumentException $e) {
            return '';
        }

        if (empty($fieldName) || empty($userId)) {
            return '';
        }

        if (empty($userAware->troughReferenceTable)) {
            return sprintf('%s.%s = %s', $targetTableAlias, $fieldName, $userId);
        }

        return sprintf(
            '%s.%s IN (SELECT %s FROM %s WHERE %s = %s)',
            $targetTableAlias,
            $userAware->troughForeignKey,
            $userAware->troughReferenceId,
            $userAware->troughReferenceTable,
            $fieldName,
            $userId
        );
    }

    /**
     * @param Reader $reader
     *
     * @return void
     */
    public function setAnnotationReader(Reader $reader): void
    {
        $this->reader = $reader;
    }
}
