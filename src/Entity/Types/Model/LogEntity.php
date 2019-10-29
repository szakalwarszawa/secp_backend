<?php

declare(strict_types=1);

namespace  App\Entity\Types\Model;

use App\Entity\Types\LogEntityInterface;
use App\Entity\Types\LoggableEntityInterface;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class LogEntity
 */
class LogEntity implements LogEntityInterface
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
     * @var LoggableEntityInterface
    */
    protected $parent;

    /**
    * @var User
    *
    * @ORM\ManyToOne(targetEntity="App\Entity\User")
    * @ORM\JoinColumn(nullable=false)
    * @Groups({"get"})
    */
    protected $owner;

    /**
    * @var DateTimeInterface
    *
    * @ORM\Column(type="datetime")
    * @Assert\NotBlank()
    * @Groups({"get"})
    */
    protected $logDate;

    /**
    * @var string
    *
    * @ORM\Column(type="text")
    * @Assert\NotBlank()
    * @Groups({"get"})
    */
    protected $notice;

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
    protected $triggerElement;

    /**
    * @return int|null
    */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
    *
    * @return LogEntityInterface|null
    */
    public function getParent(): ?LoggableEntityInterface
    {
        return $this->parent;
    }

    /**
     * @param null|LoggableEntityInterface $parent
     *
     * @return LogEntityInterface
     */
    public function setParent(?LoggableEntityInterface $parent): LogEntityInterface
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
    * @return LogEntityInterface
    */
    public function setOwner(?User $owner): LogEntityInterface
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
    * @return LogEntityInterface
    */
    public function setLogDate(DateTimeInterface $logDate): LogEntityInterface
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
