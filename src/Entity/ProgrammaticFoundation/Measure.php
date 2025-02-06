<?php

namespace App\Entity\ProgrammaticFoundation;

use App\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'programmatic_foundation_measure')]
#[UniqueEntity(fields: ['position', 'subApproach'], message: 'programmatic_foundation.unique_position.measure', errorPath: 'position')]
class Measure implements Timestampable
{
    use EntityIdentityTrait;
    use TimestampableEntity;

    #[Assert\GreaterThan(value: 0, message: 'programmatic_foundation.position.greater_than_zero')]
    #[Groups(['approach_list_read'])]
    #[ORM\Column(type: 'smallint')]
    private $position;

    #[Assert\NotBlank(message: 'programmatic_foundation.title.not_empty')]
    #[Groups(['approach_list_read'])]
    #[ORM\Column]
    private $title;

    #[Assert\NotBlank(message: 'programmatic_foundation.content.not_empty')]
    #[Groups(['approach_list_read'])]
    #[ORM\Column(type: 'text')]
    private $content;

    #[Groups(['approach_list_read'])]
    #[ORM\Column(type: 'boolean')]
    private $isLeading;

    #[Groups(['approach_list_read'])]
    #[ORM\Column(type: 'boolean')]
    private $isExpanded;

    #[Assert\NotNull(message: 'programmatic_foundation.parent.required.measure')]
    #[ORM\ManyToOne(targetEntity: SubApproach::class, inversedBy: 'measures')]
    private $subApproach;

    #[Groups(['approach_list_read'])]
    #[ORM\OneToMany(mappedBy: 'measure', targetEntity: Project::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private $projects;

    #[Groups(['approach_list_read'])]
    #[ORM\JoinTable(name: 'programmatic_foundation_measure_tag')]
    #[ORM\ManyToMany(targetEntity: Tag::class)]
    private $tags;

    public function __construct(
        ?int $position = null,
        ?string $title = null,
        ?string $content = null,
        bool $isLeading = false,
        bool $isExpanded = false,
        ?SubApproach $subApproach = null,
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
