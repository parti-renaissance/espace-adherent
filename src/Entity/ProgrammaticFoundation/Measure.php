<?php

namespace AppBundle\Entity\ProgrammaticFoundation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="programmatic_foundation_measure")
 * @UniqueEntity("slug", message="Le slug des mesures doit être unique")
 * @UniqueEntity(
 *     fields={"position", "approach"},
 *     errorPath="position",
 *     message="L'ordre d'affichage doit être unique pour un sous axe donné"
 * )
 */
class Measure
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThan(value=0, message="La position doit être supérieure à 0")
     */
    private $position;

    /**
     * @ORM\Column
     * @Assert\NotBlank(message="Le titre ne peut pas être vide")
     */
    private $title;

    /**
     * @ORM\Column(unique=true)
     * @Gedmo\Slug(fields={"title"})
     * @Assert\NotBlank(message="Le slug ne peut pas être vide")
     */
    private $slug;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Le contenu ne peut pas être vide")
     */
    private $content;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isLeading = false;

    /**
     * @ORM\Column
     * @Assert\NotBlank(message="Le ville ne peut pas être vide")
     */
    private $city;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isExpanded = false;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ProgrammaticFoundation\SubApproach", inversedBy="measures")
     * @Assert\NotNull(message="Une mesure doit être liée à un sous-axe")
     */
    private $approach;

    /**
     * @ORM\OneToMany(
     *   targetEntity="AppBundle\Entity\ProgrammaticFoundation\Project",
     *   mappedBy="measure",
     *   indexBy="id",
     *   cascade={"all"},
     *   orphanRemoval=true
     * )
     * @Assert\Valid
     */
    private $projects;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ProgrammaticFoundation\Tag")
     * @ORM\JoinTable(name="programmatic_foundation_measure_tag")
     */
    private $tags;

    public function __construct(
        int $position = null,
        string $title = null,
        string $content = null,
        bool $isLeading = false,
        string $city = null,
        bool $isExpanded = false,
        SubApproach $approach = null
    ) {
        $this->position = $position;
        $this->title = $title;
        $this->content = $content;
        $this->isLeading = $isLeading;
        $this->city = $city;
        $this->isExpanded = $isExpanded;
        $this->approach = $approach;
        $this->projects = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->title ?: '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function isLeading(): bool
    {
        return $this->isLeading;
    }

    public function setIsLeading(bool $isLeading): self
    {
        $this->isLeading = $isLeading;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getIsExpanded(): bool
    {
        return $this->isExpanded;
    }

    public function setIsExpanded(bool $isExpanded): self
    {
        $this->isExpanded = $isExpanded;

        return $this;
    }

    public function getApproach(): ?SubApproach
    {
        return $this->approach;
    }

    public function setApproach(?SubApproach $approach): self
    {
        $this->approach = $approach;

        return $this;
    }

    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function setProjects(iterable $projects): self
    {
        foreach ($projects as $project) {
            $this->addProject($project);
        }

        return $this;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setMeasure($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            if ($project->getMeasure() === $this) {
                $project->setMeasure(null);
            }
        }

        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function setTags(iterable $tags): self
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }

        return $this;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getSectionIdentifier(): string
    {
        return sprintf('%s.%d', $this->getApproach()->getSectionIdentifier(), $this->getPosition());
    }
}
