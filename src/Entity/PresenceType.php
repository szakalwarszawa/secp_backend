<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="`presence_types`",
 *     indexes={
 *          @ORM\Index(name="idx_presence_types_short_name", columns={"short_name"}),
 *          @ORM\Index(name="idx_presence_types_name", columns={"id", "name"}),
 *          @ORM\Index(name="idx_presence_types_active", columns={"active"}),
 *          @ORM\Index(name="idx_presence_types_name", columns={"active", "name"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\PresenceTypeRepository")
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
 *          "id": "exact",
 *          "name": "istart"
 *      }
 * )
 * @ApiFilter(
 *      BooleanFilter::class,
 *      properties={
 *          "isAbsence",
 *          "isTimed",
 *          "active"
 *      }
 * )
 * @ApiFilter(
 *      OrderFilter::class,
 *      properties={
 *          "id",
 *          "name"
 *      },
 *      arguments={"orderParameterName"="_order"}
 * )
 */
class PresenceType
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10, nullable=false)
     * @Assert\NotBlank();
     * @Groups({"get"})
     */
    private $shortName;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Groups({"get"})
     */
    private $name;

    /**
     * @ORM\Column(
     *     type="boolean",
     *     nullable=true,
     *     options={"default"=false}
     * )
     * @Assert\NotNull()
     * @Groups({"get"})
     */
    private $isAbsence;

    /**
     * @ORM\Column(
     *     type="boolean",
     *     nullable=true,
     *     options={"default"=true}
     * )
     * @Assert\NotNull()
     * @Groups({"get"})
     */
    private $isTimed;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Groups({"get"})
     */
    private $active;

    /**
     * @return bool|null
     */
    public function getIsAbsence(): ?bool
    {
        return $this->isAbsence;
    }

    /**
     * @param bool $isAbsence
     * @return PresenceType
     */
    public function setIsAbsence(bool $isAbsence): self
    {
        $this->isAbsence = $isAbsence;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsTimed(): ?bool
    {
        return $this->isTimed;
    }

    /**
     * @param bool $isTimed
     * @return PresenceType
     */
    public function setIsTimed(bool $isTimed): self
    {
        $this->isTimed = $isTimed;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    /**
     * @param string $shortName
     * @return PresenceType
     */
    public function setShortName(string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return PresenceType
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return PresenceType
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
