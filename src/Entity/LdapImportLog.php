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
     * @ORM\Column(type="text")
     */
    protected $result;

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
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @param string $result
     *
     * @return LdapImportLog
     */
    public function setResult(string $result): LdapImportLog
    {
        $this->result = $result;

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
