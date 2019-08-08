<?php

declare(strict_types=1);

namespace App\Ldap\Import\Updater\Result;

use App\Ldap\Import\Updater\Result\Types;
use App\Utils\ConstantsUtil;
use Symfony\Component\VarDumper\VarDumper;

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
     * Optional human-readable result message.
     *
     * @var string
     */
    private $message;

    /**
     * Action inherited from Ldap\Constants\Actions
     * @var string
     */
    private $action;

    /**
     * @param string $className
     * @param string $type
     * @param null|string $message
     */
    public function __construct(string $className, string $type, ?string $message = null, ?string $action = null)
    {
        $this->className = $className;
        $this->type = ConstantsUtil::constValue($type, Types::class);
        $this->message = $message;
        $this->action = ConstantsUtil::constValue($action, Actions::class);
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
