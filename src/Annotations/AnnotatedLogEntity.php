<?php

declare(strict_types=1);

namespace App\Annotations;

use InvalidArgumentException;

/**
 * Class AnnotatedLogEntity
 *
 * @Annotation
 */
class AnnotatedLogEntity
{
    /**
     * @var array
     */
    public const SUPPORTED_KEYS = ['message'];

    /**
     * @var array
     */
    public const REQUIRED_KEYS = ['message'];

    /**
     * Log class name.
     *
     * @var string
     */
    public $logClass;

    /**
     * Available options:
     *  - message - This is the message that will saved as log notice.
     *  Available formats - (%s - new value and second %s - new value).
     *
     * @var array
     */
    public $options = [];

    /**
     * Validate options.
     *
     * @return array
     */
    public function validateOptions(): void
    {
        foreach (array_keys($this->options) as $optionKey) {
            if (!in_array($optionKey, self::SUPPORTED_KEYS, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Option %s is invalid',
                    $optionKey
                ));
            }
            if (!in_array($optionKey, self::REQUIRED_KEYS, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Option %s is required',
                    $optionKey
                ));
            }
        }
    }
}
