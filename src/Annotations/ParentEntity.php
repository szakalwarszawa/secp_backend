<?php

declare(strict_types=1);

namespace App\Annotations;

/**
 * Class ParentEntity
 *
 * @Annotation
 */
class ParentEntity
{
    /**
     * @var null|string
     */
    public $className;

    /**
     * ParentEntity constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->className = $parameters['value'] ?? null;
    }
}
