<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\UserMeAction;
use App\Exception\SectionNotBelongToDepartmentException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\UserRole;

/**
 * @ORM\Table(
 *     name="`users`",
 *     indexes={
 *          @ORM\Index(name="idx_users_username", columns={"username"}),
 *          @ORM\Index(name="idx_users_sam_account_name", columns={"sam_account_name"}),
 *          @ORM\Index(name="idx_users_email", columns={"email"}),
 *          @ORM\Index(name="idx_users_last_name", columns={"last_name"}),
 *          @ORM\Index(name="idx_users_first_name", columns={"first_name"}),
 *          @ORM\Index(name="idx_users_department_id", columns={"department_id"}),
 *          @ORM\Index(name="idx_users_section_id", columns={"section_id"}),
 *          @ORM\Index(name="idx_users_default_work_schedule_profile_id", columns={"default_work_schedule_profile_id"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("username", errorPath="username", groups={"post", "put"})
 * @UniqueEntity("email", groups={"post", "put"})
 * @UniqueEntity("samAccountName", groups={"post", "put"})
 * @ApiResource(
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={
 *                      "get",
 *                      "get-user-with-department",
 *                      "get-user-with-section",
 *                      "get-user-with-managed-departments",
 *                      "get-user-with-managed-sections",
 *                      "get-user-with-default_work_schedule_profile"
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
 *          }
 *      },
 *      collectionOperations={
 *          "get-users-me"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "method"="GET",
 *              "path"="/users/me",
 *              "controller"=UserMeAction::class,
 *              "normalization_context"={
 *                  "groups"={
 *                      "get",
 *                      "get-user-with-department",
 *                      "get-user-with-section",
 *                      "get-user-with-default_work_schedule_profile"
 *                  }
 *              }
 *          },
 *          "get"={
 *              "normalization_context"={
 *                  "groups"={
 *                      "get",
 *                      "get-user-with-department",
 *                      "get-user-with-section",
 *                      "get-user-with-default_work_schedule_profile"
 *                  }
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
 *              "get-user-with-department",
 *              "get-user-with-section",
 *              "get-user-with-managed-departments",
 *              "get-user-with-managed-sections",
 *              "get-user-with-default_work_schedule_profile"
 *          }
 *      }
 * )
 * @ApiFilter(
 *      SearchFilter::class,
 *      properties={
 *          "id": "exact",
 *          "username": "iexact",
 *          "email": "iexact",
 *          "firstName": "istart",
 *          "lastName": "istart",
 *          "title": "ipartial",
 *          "department.id",
 *          "section.id",
 *          "defaultWorkScheduleProfile.id"
 *      }
 * )
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={
 *         "id",
 *         "samAccountName",
 *         "username",
 *         "email",
 *         "firstName",
 *         "lastName",
 *         "department.name",
 *         "section.name",
 *         "defaultWorkScheduleProfile.id"
 *     },
 *     arguments={"orderParameterName"="_order"}
 * )
 */
class User implements UserInterface
{
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get", "get-department-with-users", "get-section-with-users", "get-section-with-managers"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=256)
     * @Assert\Length(max=256, groups={"post"})
     * @Groups({"get", "put", "post"})
     */
    private $samAccountName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Length(min=6, max=255, groups={"post"})
     * @Groups({"get", "put", "post"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=180, unique=true, nullable=true)
     * @Assert\Email()
     * @Groups({"get", "post", "put"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Length(min=3, max=255, groups={"post", "put"})
     * @Groups({"get", "post", "put"})
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Length(min=3, max=255, groups={"post", "put"})
     * @Groups({"get", "post", "put"})
     */
    private $lastName;

    /**
     * @ORM\Column(type="simple_array", length=255)
     * @Groups({"get", "post", "admin-supervisor:input"})
     * @UserRole
     */
    private $roles = [];

    /**
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Length(max=256)
     * @Groups({"post", "put"})
     */
    private $plainPassword;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", length=256, nullable=true)
     * @Groups({"user_prohibited"})
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, groups={"post"})
     * @Groups({"get", "put", "post"})
     */
    private $distinguishedName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, groups={"post"})
     * @Groups({"get", "put", "post"})
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"put", "post", "get-user-with-department", "Department-get_get-user-with-department"})
     */
    private $department;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Section", inversedBy="users")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"put", "post", "get-user-with-section"})
     */
    private $section;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Department", mappedBy="managers")
     * @Groups({"get-user-with-managed-departments", "put"})
     */
    private $managedDepartments;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Section", mappedBy="managers")
     * @Groups({"get-user-with-managed-sections", "put"})
     */
    private $managedSections;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\WorkScheduleProfile")
     * @ORM\JoinColumn(nullable=false, columnDefinition="INT NOT NULL DEFAULT 1")
     * @Groups({"put", "post", "get-user-with-default_work_schedule_profile"})
     */
    private $defaultWorkScheduleProfile;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=5,
     *     nullable=false,
     *     columnDefinition="VARCHAR(5) NOT NULL DEFAULT '07:30'",
     *     options={"default"="07:30"}
     * )
     * @Assert\NotNull()
     * @Groups({"get", "put"})
     */
    private $dayStartTimeFrom = '07:30';

    /**
     * @ORM\Column(
     *     type="string",
     *     length=5,
     *     nullable=false,
     *     columnDefinition="VARCHAR(5) NOT NULL DEFAULT '07:30'",
     *     options={"default"="07:30"}
     * )
     * @Assert\NotNull()
     * @Groups({"get", "put"})
     */
    private $dayStartTimeTo = '07:30';

    /**
     * @ORM\Column(
     *     type="string",
     *     length=5,
     *     nullable=false,
     *     columnDefinition="VARCHAR(5) NOT NULL DEFAULT '16:30'",
     *     options={"default"="16:30"}
     * )
     * @Assert\NotNull()
     * @Groups({"get", "put"})
     */
    private $dayEndTimeFrom = '16:30';

    /**
     * @ORM\Column(
     *     type="string",
     *     length=5,
     *     nullable=false,
     *     columnDefinition="VARCHAR(5) NOT NULL DEFAULT '16:30'",
     *     options={"default"="16:30"}
     * )
     * @Assert\NotNull()
     * @Groups({"get", "put"})
     */
    private $dayEndTimeTo = '16:30';

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=4,
     *     scale=2,
     *     nullable=false,
     *     columnDefinition="NUMERIC(4, 2) NOT NULL DEFAULT 8.00",
     *     options={"default"=8.00}
     * )
     * @Assert\NotNull()
     * @Groups({"get", "put"})
     */
    private $dailyWorkingTime = 8.00;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserTimesheet", mappedBy="owner")
     */
    private $userTimesheets;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->managedDepartments = new ArrayCollection();
        $this->managedSections = new ArrayCollection();
        $this->userTimesheets = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return User
     */
    public function setTitle($title): User
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getSamAccountName(): string
    {
        return $this->samAccountName;
    }

    /**
     * @param string $samAccountName
     *
     * @return User
     */
    public function setSamAccountName($samAccountName): User
    {
        $this->samAccountName = $samAccountName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     *
     * @return User
     */
    public function setPlainPassword($plainPassword): User
    {
        $this->plainPassword = $plainPassword;
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
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     *
     * @return User
     */
    public function setEmail(?string $email = null): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @return string
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string)$this->username;
    }

    /**
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username): User
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return array
     *
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        return array_unique($roles);
    }

    /**
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return string|null
     *
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     *
     * @return User
     */
    public function setPassword(?string $password = null): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @return void
     *
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Department[]
     */
    public function getManagedDepartments(): Collection
    {
        return $this->managedDepartments;
    }

    /**
     * @param Department $managedDepartment
     *
     * @return User
     */
    public function addManagedDepartment(Department $managedDepartment): User
    {
        if (!$this->managedDepartments->contains($managedDepartment)) {
            $this->managedDepartments[] = $managedDepartment;
            $managedDepartment->addManager($this);
        }

        return $this;
    }

    /**
     * @param Department $managedDepartment
     *
     * @return User
     */
    public function removeManagedDepartment(Department $managedDepartment): User
    {
        if ($this->managedDepartments->contains($managedDepartment)) {
            $this->managedDepartments->removeElement($managedDepartment);
            $managedDepartment->removeManager($this);
        }

        return $this;
    }

    /**
     * @return Collection|Section[]
     */
    public function getManagedSections(): Collection
    {
        return $this->managedSections;
    }

    /**
     * @param Section $managedSection
     *
     * @return User
     */
    public function addManagedSection(Section $managedSection): User
    {
        if (!$this->managedSections->contains($managedSection)) {
            $this->managedSections[] = $managedSection;
            $managedSection->addManager($this);
        }

        return $this;
    }

    /**
     * @param Section $managedSection
     *
     * @return User
     */
    public function removeManagedSection(Section $managedSection): User
    {
        if ($this->managedSections->contains($managedSection)) {
            $this->managedSections->removeElement($managedSection);
            $managedSection->removeManager($this);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDistinguishedName(): ?string
    {
        return $this->distinguishedName;
    }

    /**
     * @param string $distinguishedName
     *
     * @return User
     */
    public function setDistinguishedName($distinguishedName): User
    {
        $this->distinguishedName = $distinguishedName;
        return $this;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreFlush()
     *
     * @return void
     *
     * @throws SectionNotBelongToDepartmentException
     */
    public function checkSameSectionAsDepartmentValidate(): void
    {
        if ($this->getSection() !== null &&
            $this->getDepartment() !== null &&
            !$this->getDepartment()->getSections()->contains($this->getSection())
        ) {
            throw new SectionNotBelongToDepartmentException();
        }
    }

    /**
     * @return Section|null
     */
    public function getSection(): ?Section
    {
        return $this->section;
    }

    /**
     * @param Section|null $section
     *
     * @return User
     */
    public function setSection(?Section $section): User
    {
        $this->section = $section;
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
     * @return User
     */
    public function setDepartment(?Department $department): User
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return WorkScheduleProfile|null
     */
    public function getDefaultWorkScheduleProfile(): ?WorkScheduleProfile
    {
        return $this->defaultWorkScheduleProfile;
    }

    /**
     * @param WorkScheduleProfile|null $defaultWorkScheduleProfile
     *
     * @return User
     */
    public function setDefaultWorkScheduleProfile(?WorkScheduleProfile $defaultWorkScheduleProfile): User
    {
        $this->defaultWorkScheduleProfile = $defaultWorkScheduleProfile;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDayStartTimeFrom(): ?string
    {
        return $this->dayStartTimeFrom;
    }

    /**
     * @param string $dayStartTimeFrom
     *
     * @return User
     */
    public function setDayStartTimeFrom(string $dayStartTimeFrom): User
    {
        $this->dayStartTimeFrom = $dayStartTimeFrom;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDayStartTimeTo(): ?string
    {
        return $this->dayStartTimeTo;
    }

    /**
     * @param string $dayStartTimeTo
     *
     * @return User
     */
    public function setDayStartTimeTo(string $dayStartTimeTo): User
    {
        $this->dayStartTimeTo = $dayStartTimeTo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDayEndTimeFrom(): ?string
    {
        return $this->dayEndTimeFrom;
    }

    /**
     * @param string $dayEndTimeFrom
     *
     * @return User
     */
    public function setDayEndTimeFrom(string $dayEndTimeFrom): User
    {
        $this->dayEndTimeFrom = $dayEndTimeFrom;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDayEndTimeTo(): ?string
    {
        return $this->dayEndTimeTo;
    }

    /**
     * @param string $dayEndTimeTo
     *
     * @return User
     */
    public function setDayEndTimeTo(string $dayEndTimeTo): User
    {
        $this->dayEndTimeTo = $dayEndTimeTo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDailyWorkingTime()
    {
        return $this->dailyWorkingTime;
    }

    /**
     * @param $dailyWorkingTime
     *
     * @return User
     */
    public function setDailyWorkingTime($dailyWorkingTime): User
    {
        $this->dailyWorkingTime = $dailyWorkingTime;

        return $this;
    }

    /**
     * @return Collection|UserTimesheet[]
     */
    public function getUserTimesheets(): Collection
    {
        return $this->userTimesheets;
    }

    /**
     * @param UserTimesheet $userTimesheet
     *
     * @return User
     */
    public function addUserTimesheet(UserTimesheet $userTimesheet): User
    {
        if (!$this->userTimesheets->contains($userTimesheet)) {
            $this->userTimesheets[] = $userTimesheet;
            $userTimesheet->setOwner($this);
        }

        return $this;
    }

    /**
     * @param UserTimesheet $userTimesheet
     *
     * @return User
     */
    public function removeUserTimesheet(UserTimesheet $userTimesheet): User
    {
        if ($this->userTimesheets->contains($userTimesheet)) {
            $this->userTimesheets->removeElement($userTimesheet);
            // set the owning side to null (unless already changed)
            if ($userTimesheet->getOwner() === $this) {
                $userTimesheet->setOwner(null);
            }
        }

        return $this;
    }
}
