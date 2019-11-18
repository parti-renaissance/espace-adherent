<?php

namespace AppBundle\Entity\ProgrammaticFoundation;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityIdentityTrait;
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
 * @ORM\Table(name="programmatic_foundation_sub_approach")
 *
 * @UniqueEntity(
 *     fields={"position", "approach"},
 *     errorPath="position",
 *     message="programmatic_foundation.unique_position.sub_approach"
 * )
 *
 * @Algolia\Index(autoIndex=false)
 */
class SubApproach
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
     * @ORM\Column(nullable=true)
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $subtitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $content;

    /**
     * @ORM\Column(type="boolean")
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $isExpanded;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ProgrammaticFoundation\Approach", inversedBy="subApproaches")
     * @ORM\OrderBy({"position": "ASC"})
     * @Assert\NotNull(message="programmatic_foundation.parent.required.sub_approach")
     */
    private $approach;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\ProgrammaticFoundation\Measure",
     *     mappedBy="subApproach",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     * @Assert\Valid
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $measures;

    public function __construct(
        int $position = null,
        string $title = null,
        string $subtitle = null,
        string $content = null,
        bool $isExpanded = false,
        Approach $approach = null
    ) {
        $this->uuid = Uuid::uuid4();
        $this->position = $position;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->content = $content;
        $this->isExpanded = $isExpanded;
        $this->approach = $approach;

        $this->measures = new ArrayCollection();
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

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getIsExpanded(): bool
    {
        return $this->isExpanded;
    }

    public function setIsExpanded(bool $isExpanded): void
    {
        $this->isExpanded = $isExpanded;
    }

    public function getApproach(): ?Approach
    {
        return $this->approach;
    }

    public function setApproach(?Approach $approach): void
    {
        $this->approach = $approach;
    }

    public function getMeasures(): Collection
    {
        return $this->measures;
    }

    public function setMeasures(iterable $measures): void
    {
        foreach ($measures as $measure) {
            $this->addMeasure($measure);
        }
    }

    public function addMeasure(Measure $measure): void
    {
        if (!$this->measures->contains($measure)) {
            $this->measures->add($measure);
            $measure->setSubApproach($this);
        }
    }

    public function removeMeasure(Measure $measure): void
    {
        if ($this->measures->contains($measure)) {
            $this->measures->removeElement($measure);
            if ($measure->getSubApproach() === $this) {
                $measure->setSubApproach(null);
            }
        }
    }
}
