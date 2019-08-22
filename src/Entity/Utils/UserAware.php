<?php
declare(strict_types=1);

namespace App\Entity\Utils;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class UserAware
 * @package App\Entity\Utils
 *
 * @Annotation
 * @Target("CLASS")
 */
class UserAware
{
    /**
     * @var string
     */
    public $userFieldName;

    /**
     * @var string
     */
    public $troughForeignKey;

    /**
     * @var string
     */
    public $troughReferenceTable;

    /**
     * @var string
     */
    public $troughReferenceId;
}
