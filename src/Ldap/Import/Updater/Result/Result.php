<?php

declare(strict_types=1);

namespace App\Ldap\Import\Updater\Result;

use App\Ldap\Import\Updater\Result\Types;
use App\Utils\ConstantsUtil;

/**
 * Class Result
 */
class Result
{
    /**
     * Subject class name.
     *
     * @var string
     */
    private $className;

    /**
     * Result type inherited from Ldap\Constants\ImportResources
     *
     * @var string
     */
    private $type;

    /**
     * What has been updated/crated.
     *
     * @var string
     */
    private $target;

    /**
     * Optional human-readable result message.
     *
     * @var string
     */
    private $message;

    /**
     * Action inherited from Ldap\Constants\Actions
     *
     * @var string
     */
    private $action;

    /**
     * @param string $className
     * @param string $type
     * @param string $target
     * @param null|string $message
     * @param null|string $action
     */
    public function __construct(
        string $className,
        string $type,
        string $target,
        ?string $message = null,
        ?string $action = null
    ) {
        ConstantsUtil::constCheckValue($action, Actions::class);
        ConstantsUtil::constCheckValue($type, Types::class);

        $this->className = $className;
        $this->type = $type;
        $this->target = $target;
        $this->message = $message;
        $this->action = $action;
    }

    /**
     * Get className
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get target
     *
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Get action
     *
     * @return null|string
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * Get message
     *
     * @return null|string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }
}
