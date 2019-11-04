<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     schema="dictionary",
 *     name="`property_based_roles`",
 *     indexes={
 *          @ORM\Index(name="idx_property_based_roles_ldap_value", columns={"ldap_value"}),
 *          @ORM\Index(name="idx_property_based_roles_role_id", columns={"role_id"}),
 *          @ORM\Index(name="idx_property_based_roles_overridable", columns={"overridable"}),
 *          @ORM\Index(name="idx_property_based_roles_overridable", columns={"based_on"})
 *     }
 * )
 * @ORM\Entity()
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
 *
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "basedOn": "exact",
 *          "ldapValue": "exact",
 *          "role.name": "exact"
 *      }
 * )
 *
 * @ApiFilter(
 *      BooleanFilter::class,
 *      properties={
 *          "overridable"
 *      }
 * )
 */
class PropertyBasedRole
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    protected $id;

    /**
     * AD `position` value, ex. `dyrektor`
     *
     * @var null|string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"get"})
     */
    protected $ldapValue;

    /**
     * Framework role
     *
     * @var Role
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     * @Groups({"get"})
     */
    protected $role;

    /**
     * If user had this role, he should keep it.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"get"})
     */
    protected $overridable = false;

    /**
     * Role based on user`s section, position, username, department.
     * (section = info, position = title, username = samaccountname - LDAP attributes)
     *
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     * @Groups({"get"})
     */
    protected $basedOn;

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
    public function getLdapValue(): ?string
    {
        return $this->ldapValue;
    }

    /**
     * @param null|string $ldapValue
     *
     * @return PropertyBasedRole
     */
    public function setLdapValue(?string $ldapValue): PropertyBasedRole
    {
        $this->ldapValue = $ldapValue;

        return $this;
    }

    /**
     * @return null|Role
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * @param Role $role
     *
     * @return PropertyBasedRole
     */
    public function setRole(Role $role): PropertyBasedRole
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return null|bool
     */
    public function isOverridable(): ?bool
    {
        return $this->overridable;
    }

    /**
     * @param bool $overridable
     *
     * @return PropertyBasedRole
     */
    public function setOverridable(bool $overridable): PropertyBasedRole
    {
        $this->overridable = $overridable;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getBasedOn(): ?string
    {
        return $this->basedOn;
    }

    /**
     * @param string $basedOn
     *
     * @return PropertyBasedRole
     */
    public function setBasedOn(string $basedOn): PropertyBasedRole
    {
        $this->basedOn = $basedOn;

        return $this;
    }
}
