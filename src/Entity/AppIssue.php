<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Validator\RedmineTask;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     itemOperations={
 *          "get"={"security"="is_granted('ROLE_ADMIN')"},
 *      },
 *      collectionOperations={
 *          "post"={
 *              "normalization_context"={
 *                  "groups"={"post"}
 *              }
 *          }
 *      },
 *      normalizationContext={
 *          "groups"={
 *              "post"
 *          }
 *      }
 * )
 *
 * @ORM\Table(
 *     name="app_issues",
 *     schema="logs"
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\AppIssueRepository")
 * @Assert\GroupSequence({"AppIssue", "delay"})
 * @RedmineTask(groups={"delay"})
 */
class AppIssue
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"post"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"post"})
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=1,
     *     max=255
     *   )
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Groups({"post"})
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=1,
     *     max=5000
     *   )
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"post"})
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=1,
     *     max=255
     *   )
     */
    private $reporterName;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"post"})
     */
    private $redmineTaskId;

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
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return AppIssue
     */
    public function setSubject(string $subject): AppIssue
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return AppIssue
     */
    public function setDescription(string $description): AppIssue
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReporterName(): ?string
    {
        return $this->reporterName;
    }

    /**
     * @param string $reporterName
     *
     * @return AppIssue
     */
    public function setReporterName(string $reporterName): AppIssue
    {
        $this->reporterName = $reporterName;

        return $this;
    }

    public function getRedmineTaskId(): ?int
    {
        return $this->redmineTaskId;
    }

    /**
     * @param null|int $redmineTaskId
     *
     * @return AppIssue
     */
    public function setRedmineTaskId(?int $redmineTaskId): AppIssue
    {
        $this->redmineTaskId = $redmineTaskId;

        return $this;
    }
}
