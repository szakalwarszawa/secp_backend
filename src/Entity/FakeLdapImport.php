<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\LdapImportAction;
use ApiPlatform\Core\Annotation\ApiProperty;

/**
 * `Fake entity` class to not disper the configuration at
 *  the same time in the routing and the resource configuration.
 *
 * @ApiResource(collectionOperations={
 *     "get",
 *     "ldap_import"={
 *         "security"="is_granted('ROLE_ADMIN')",
 *         "method"="GET",
 *         "path"="/ldap/import",
 *         "controller"=LdapImportAction::class,
 *         "read"=false
 *          }
 * })
 */
class FakeLdapImport
{
    /**
     * @var string
     *
     * @ApiProperty(identifier=true)
     */
    protected $resourceName;

    /**
     * @var int
     */
    protected $succeedCount;

    /**
     * @var int
     */
    protected $failedCount;

    /**
     * @var array
     */
    protected $succeedDetails;

    /**
     * @var array
     */
    protected $failedDetails;

    /**
     * Set succeed count
     *
     * @param array $succeedCount
     *
     * @return FakeLdapImport
     */
    public function setSucceedCount(int $succeedCount): FakeLdapImport
    {
        $this->succeedCount = $succeedCount;

        return $this;
    }

    /**
     * Get succeedCount
     *
     * @return array
     */
    public function getSucceedCount(): int
    {
        return $this->succeedCount;
    }

    /**
     * Get failedCount
     *
     * @return array
     */
    public function getFailedCount(): int
    {
        return $this->failedCount;
    }

    /**
     * Set failedCount
     *
     * @param array $failedCount
     *
     * @return FakeLdapImport
     */
    public function setFailedCount(int $failedCount): FakeLdapImport
    {
        $this->failedCount = $failedCount;

        return $this;
    }

    /**
     * Get succeedDetails
     *
     * @return array
     */
    public function getSucceedDetails(): array
    {
        return $this->succeedDetails;
    }

    /**
     * Set succeedDetails
     *
     * @param array $succeedDetails
     *
     * @return FakeLdapImport
     */
    public function setSucceedDetails(array $succeedDetails): FakeLdapImport
    {
        $this->succeedDetails = $succeedDetails;

        return $this;
    }

    /**
     * Get failedDetails
     *
     * @return array
     */
    public function getFailedDetails(): array
    {
        return $this->failedDetails;
    }

    /**
     * Set failedDetails
     *
     * @param array $failedDetails
     *
     * @return FakeLdapImport
     */
    public function setFailedDetails(array $failedDetails): FakeLdapImport
    {
        $this->failedDetails = $failedDetails;

        return $this;
    }

    /**
     * Get resourceName
     *
     * @return string
     */
    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    /**
     * Set resourceName
     *
     * @param string $resourceName
     *
     * @return FakeLdapImport
     */
    public function setResourceName(string $resourceName): FakeLdapImport
    {
        $this->resourceName = $resourceName;

        return $this;
    }
}
