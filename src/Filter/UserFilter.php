<?php
declare(strict_types=1);

namespace App\Filter;

use App\Entity\Utils\UserAware;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\ClassMetaData;
use Doctrine\ORM\Query\Filter\SQLFilter;
use App\Filter\Query\AccessLevel;

/**
 * Class UserFilter
 */
class UserFilter extends SQLFilter
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var AccessLevel
     */
    private $accessLevel;

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

        if (!$this->accessLevel) {
            return '';
        }

        if (empty($userAware->userFieldName)) {
            return '';
        }

        if (empty($userAware->troughReferenceTable)) {
            return $this
                ->accessLevel
                ->getQuery($targetTableAlias, $userAware)
            ;
        }

        return $this
            ->accessLevel
            ->getQuery($targetTableAlias, $userAware, true)
        ;
    }

    /**
     * @param AccessLevel $accessLevel
     *
     * @return UserFilter
     */
    public function setAccessLevel(AccessLevel $accessLevel): UserFilter
    {
        $this->accessLevel = $accessLevel;

        return $this;
    }

    /**
     * @param Reader $reader
     *
     * @return UserFilter
     */
    public function setAnnotationReader(Reader $reader): UserFilter
    {
        $this->reader = $reader;

        return $this;
    }
}
