<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Types\LogEntityInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeInterface;

/**
 * @ORM\Table(
 *     schema="logs",
 *     name="user_logs",
 *     indexes={
 *          @ORM\Index(name="idx_user_log_date", columns={"log_date"}),
 *          @ORM\Index(name="idx_user_log_parent_id", columns={"parent_id"}),
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserLogRepository")
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
 */
class UserLog implements LogEntityInterface
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="logs")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=false)
     */
    private $parent;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get"})
     */
    private $owner;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Groups({"get"})
     */
    private $logDate;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups({"get"})
     */
    private $notice;

    /**
     * @var string|null
     *
     * @ORM\Column(
     *  type="string",
     *  length=100,
     *  nullable=true
     * )
     * @Groups({"get"})
     */
    private $triggerElement;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     *
     * @return User|null
     */
    public function getParent(): ?User
    {
        return $this->parent;
    }

    /**
     * @param User|null $parent
     *
     * @return UserLog
     */
    public function setParent(?User $parent): UserLog
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User|null $owner
     *
     * @return UserLog
     */
    public function setOwner(?User $owner): UserLog
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getLogDate(): DateTimeInterface
    {
        return $this->logDate;
    }

    /**
     * @param DateTimeInterface $logDate
     *
     * @return UserLog
     */
    public function setLogDate(DateTimeInterface $logDate): UserLog
    {
        $this->logDate = $logDate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNotice(): ?string
    {
        return $this->notice;
    }

    /**
     * @param string $notice
     *
     * @return LogEntityInterface
     */
    public function setNotice(string $notice): LogEntityInterface
    {
        $this->notice = $notice;

        return $this;
    }

    /**
     * @param string|null $triggerElement
     *
     * @return LogEntityInterface
     */
    public function setTriggerElement(?string $triggerElement): LogEntityInterface
    {
        $this->triggerElement = $triggerElement;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTriggerElement(): ?string
    {
        return $this->triggerElement;
    }
}
