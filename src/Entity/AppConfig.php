<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\AppConfigAction;
use ApiPlatform\Core\Annotation\ApiProperty;

/**
 * @ApiResource(collectionOperations={
 *     "get",
 *     "ldap_import"={
 *         "method"="GET",
 *         "path"="/app_config",
 *         "controller"=AppConfigAction::class,
 *         "read"=false
 *          }
 * })
 */
class AppConfig
{
    /**
     * @var string
     *
     * @ApiProperty(identifier=true)
     */
    protected $configKey;

    /**
     * @var string
     */
    protected $configValue;

    /**
     * @return string
     */
    public function getConfigKey(): string
    {
        return $this->configKey;
    }

    /**
     * @param string $configKey
     *
     * @return AppConfig
     */
    public function setConfigKey(string $configKey): AppConfig
    {
        $this->configKey = $configKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfigValue(): string
    {
        return $this->configValue;
    }

    /**
     * @param string $configValue
     *
     * @return AppConfig
     */
    public function setConfigValue(string $configValue): AppConfig
    {
        $this->configValue = $configValue;
        return $this;
    }
}
