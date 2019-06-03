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
 * @ORM\Table(name="`departments`")
 * @ORM\Entity(repositoryClass="App\Repository\DepartmentRepository")
 * @ApiResource(
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={
 *                      "get",
 *                      "get-department-with-users",
 *                      "get-department-with-sections",
 *                      "get-department-with-managers"
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
 *                  "groups"={"get", "get-department-with-sections", "get-user-with-department"}
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
 *              "get-department-with-users",
 *              "get-department-with-sections",
 *              "get-department-with-managers"
 *          }
 *      }
 * )
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "id": "exact",
 *          "name": "ipartial",
 *          "shortName": "iexact",
 *          "active": "exact"
 *      }
 * )
 */
class Department
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get", "get-user-with-department", "get-section-with-department"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)\
     * @Assert\NotBlank(groups={"post"})
     * @Groups({"get", "post", "put"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"post"})
     * @Groups({"get", "post", "put"})
     */
    private $shortName;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotBlank(groups={"post"})
     * @Groups({"get", "post", "put"})
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="department")
     * @Groups({"get-department-with-users"})
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Section", mappedBy="department")
     * @Groups({"get-department-with-sections"})
     */
    private $sections;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="managedDepartments")
     * @Groups({"get-department-with-managers"})
     */
    private $managers;

    /**
     * Department constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->sections = new ArrayCollection();
        $this->managers = new ArrayCollection();
    }

    /**
     * @return integer|null
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
     * @return Department
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
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
     * @return Department
     */
    public function setShortName(string $shortName): self
    {
        $this->shortName = $shortName;
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
     * @return Department
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
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
     * @return Department
     */
    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setDepartment($this);
        }

        return $this;
    }

    /**
     * @param User $user
     * @return Department
     */
    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getDepartment() === $this) {
                $user->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Section[]
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    /**
     * @param Section $section
     * @return Department
     */
    public function addSection(Section $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->setDepartment($this);
        }

        return $this;
    }

    /**
     * @param Section $section
     * @return Department
     */
    public function removeSection(Section $section): self
    {
        if ($this->sections->contains($section)) {
            $this->sections->removeElement($section);
            // set the owning side to null (unless already changed)
            if ($section->getDepartment() === $this) {
                $section->setDepartment(null);
            }
        }

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
     * @return Department
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
     * @return Department
     */
    public function removeManager(User $manager): self
    {
        if ($this->managers->contains($manager)) {
            $this->managers->removeElement($manager);
        }

        return $this;
    }
}
