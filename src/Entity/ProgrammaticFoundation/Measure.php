<?php

namespace App\Entity\ProgrammaticFoundation;

use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="programmatic_foundation_measure")
 *
 * @UniqueEntity(
 *     fields={"position", "subApproach"},
 *     errorPath="position",
 *     message="programmatic_foundation.unique_position.measure"
 * )
 */
class Measure
{
    use EntityIdentityTrait;
    use TimestampableEntity;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThan(value=0, message="programmatic_foundation.position.greater_than_zero")
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $position;

    /**
     * @ORM\Column
     * @Assert\NotBlank(message="programmatic_foundation.title.not_empty")
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="programmatic_foundation.content.not_empty")
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $content;

    /**
     * @ORM\Column(type="boolean")
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $isLeading;

    /**
     * @ORM\Column(type="boolean")
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $isExpanded;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProgrammaticFoundation\SubApproach", inversedBy="measures")
     * @Assert\NotNull(message="programmatic_foundation.parent.required.measure")
     */
    private $subApproach;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ProgrammaticFoundation\Project",
     *     mappedBy="measure",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"position": "ASC"})
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $projects;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\ProgrammaticFoundation\Tag")
     * @ORM\JoinTable(name="programmatic_foundation_measure_tag")
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $tags;

    public function __construct(
        ?int $position = null,
        ?string $title = null,
        ?string $content = null,
        bool $isLeading = false,
        bool $isExpanded = false,
        ?SubApproach $subApproach = null
    ) {
        $this->uuid = Uuid::uuid4();
        $this->position = $position;
        $this->title = $title;
        $this->content = $content;
        $this->isLeading = $isLeading;
        $this->isExpanded = $isExpanded;
        $this->subApproach = $subApproach;
        $this->projects = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function isLeading(): bool
    {
        return $this->isLeading;
    }

    public function setIsLeading(bool $isLeading): void
    {
        $this->isLeading = $isLeading;
    }

    public function getIsExpanded(): bool
    {
        return $this->isExpanded;
    }

    public function setIsExpanded(bool $isExpanded): void
    {
        $this->isExpanded = $isExpanded;
    }

    public function getSubApproach(): ?SubApproach
    {
        return $this->subApproach;
    }

    public function setSubApproach(?SubApproach $subApproach): void
    {
        $this->subApproach = $subApproach;
    }

    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function setProjects(iterable $projects): void
    {
        foreach ($projects as $project) {
            $this->addProject($project);
        }
    }

    public function addProject(Project $project): void
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setMeasure($this);
        }
    }

    public function removeProject(Project $project): void
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            if ($project->getMeasure() === $this) {
                $project->setMeasure(null);
            }
        }
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function setTags(iterable $tags): void
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }

    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
    }

    public function removeTag(Tag $tag): void
    {
        $this->tags->removeElement($tag);
    }
}
