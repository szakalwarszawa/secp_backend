<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *     name="`sections`",
 *     indexes={
 *          @ORM\Index(name="idx_sections_name", columns={"name"}),
 *          @ORM\Index(name="idx_sections_active", columns={"active"}),
 *          @ORM\Index(name="idx_sections_department_id", columns={"department_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\SectionRepository")
 * @ApiResource(
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={
 *                      "get",
 *                      "get-section-with-users",
 *                      "get-section-with-department",
 *                      "get-section-with-managers"
 *                  }
 *              }
 *          },
 *          "put"={
 *              "normalization_context"={
 *                  "groups"={"get"}
 *              },
 *              "denormalization_context"={
 *                  "groups"={"put"}
 *              }
 *          },
 *      },
 *      collectionOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={"get", "get-section-with-department"}
 *              },
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={"post"}
 *              },
 *              "normalization_context"={
 *                  "groups"={"get"}
 *              },
 *              "validation_groups"={"post"}
 *          }
 *      },
 *      normalizationContext={
 *          "groups"={
 *              "get",
 *              "get-section-with-department",
 *              "get-section-with-managers"
 *          }
 *      }
 * )
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "id": "exact",
 *          "name": "ipartial",
 *          "active": "exact"
 *      }
 * )
 */
class Section
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get", "get-user-with-section", "get-department-with-sections"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"post"})
     * @Groups({"get", "post", "put"})
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotBlank(groups={"post"})
     * @Groups({"get", "post", "put"})
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="section")
     * @Groups({"get-section-with-users", "put"})
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="sections")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get-section-with-department", "put", "post"})
     */
    private $department;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="managedSections")
     * @ORM\JoinTable(name="section_managers")
     * @Groups({"get-section-with-managers", "put", "post"})
     */
    private $managers;

    /**
     * Section constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->managers = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Section
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param User $user
     *
     * @return Section
     */
    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setSection($this);
        }

        return $this;
    }

    /**
     * @param User $user
     *
     * @return Section
     */
    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getSection() === $this) {
                $user->setSection(null);
            }
        }

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
     *
     * @return Section
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Department|null
     */
    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    /**
     * @param Department|null $department
     *
     * @return Section
     */
    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getManagers(): Collection
    {
        return $this->managers;
    }

    /**
     * @param User $manager
     *
     * @return Section
     */
    public function addManager(User $manager): self
    {
        if (!$this->managers->contains($manager)) {
            $this->managers[] = $manager;
        }

        return $this;
    }

    /**
     * @param User $manager
     *
     * @return Section
     */
    public function removeManager(User $manager): self
    {
        if ($this->managers->contains($manager)) {
            $this->managers->removeElement($manager);
        }

        return $this;
    }
}
