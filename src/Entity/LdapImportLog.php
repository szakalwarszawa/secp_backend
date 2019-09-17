<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;

/**
 * Entity class LdapImportLog
 *
 * @ORM\Table(
 *     schema="logs",
 *     name="`ldap_import_log`"
 * )
 * @ORM\Entity()
 */
class LdapImportLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="byte_object")
     */
    protected $importResult;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @return null|int
     */
    protected function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getImportResult(): string
    {
        return $this->importResult;
    }

    /**
     * @param string $importResult
     *
     * @return LdapImportLog
     */
    public function setImportResult(string $importResult): LdapImportLog
    {
        $this->importResult = $importResult;

        return $this;
    }

    /**
     * @return null|DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface $createdAt
     *
     * @return LdapImportLog
     */
    public function setCreatedAt(DateTimeInterface $createdAt): LdapImportLog
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
