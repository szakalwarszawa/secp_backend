<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValueExists extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Entity {{ class }} does not contain specified value ({{ value }})';

    /**
     * @var string
     *
     * Entity in which the value will be searched.
     * ex. 'App\Entity\SampleEntity`
     */
    public $entity;

    /**
     * @var string
     *
     * Field name by which value will be searched.
     * ex. `name`.
     */
    public $searchField;

    /**
     * Custom element values that are also valid but do not exist in the database.
     *
     * @var array
     */
    public $customElements = [];
}
