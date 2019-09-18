<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImportLdapLogRepository")
 * @ORM\Table(
 *     schema="logs",
 *     name="`import_ldap_logs`",
 *     indexes={
 *          @ORM\Index(name="idx_import_ldap_log_resource_name", columns={"resource_name"}),
 *          @ORM\Index(name="idx_import_ldap_log_created_at", columns={"created_at"})
 *     }
 * )
 * @ApiResource(
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={
 *                      "get"
 *                  }
 *              }
 *          }
 *      },
 *      collectionOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={"get"}
 *              }
 *          }
 *      },
 *      normalizationContext={
 *          "groups"={
 *              "get"
 *          }
 *      }
 * )
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "resourceName": "ipartial"
 *      }
 * )
 */
class ImportLdapLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Groups({"get"})
     */
    protected $resourceName;

    /**
     * @var string
     *
     * @ORM\Column(type="json_array", nullable=true)
     * @Groups({"get"})
     */
    protected $succeedElements;

    /**
     * @var string
     *
     * @ORM\Column(type="json_array", nullable=true)
     * @Groups({"get"})
     */
    protected $failedElements;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     * @Groups({"get"})
     */
    protected $createdAt;

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getResourceName(): ?string
    {
        return $this->resourceName;
    }

    /**
     * @param string $resourceName
     *
     * @return ImportLdapLog
     */
    public function setResourceName(string $resourceName): ImportLdapLog
    {
        $this->resourceName = $resourceName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSucceedElements(): ?string
    {
        return $this->succeedElements;
    }

    /**
     * @param null|string $succeedElements
     *
     * @return ImportLdapLog
     */
    public function setSucceedElements(?string $succeedElements): ImportLdapLog
    {
        $this->succeedElements = $succeedElements;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFailedElements(): ?string
    {
        return $this->failedElements;
    }

    /**
     * @param null|string $failedElements
     *
     * @return ImportLdapLog
     */
    public function setFailedElements(?string $failedElements): ImportLdapLog
    {
        $this->failedElements = $failedElements;

        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface $createdAt
     *
     * @return ImportLdapLog
     */
    public function setCreatedAt(DateTimeInterface $createdAt): ImportLdapLog
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
