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
#[ORM\Table(name: 'programmatic_foundation_sub_approach')]
#[UniqueEntity(fields: ['position', 'approach'], message: 'programmatic_foundation.unique_position.sub_approach', errorPath: 'position')]
class SubApproach implements Timestampable
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

    #[Groups(['approach_list_read'])]
    #[ORM\Column(nullable: true)]
    private $subtitle;

    #[Groups(['approach_list_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    #[Groups(['approach_list_read'])]
    #[ORM\Column(type: 'boolean')]
    private $isExpanded;

    #[Assert\NotNull(message: 'programmatic_foundation.parent.required.sub_approach')]
    #[ORM\ManyToOne(targetEntity: Approach::class, inversedBy: 'subApproaches')]
    private $approach;

    #[Groups(['approach_list_read'])]
    #[ORM\OneToMany(mappedBy: 'subApproach', targetEntity: Measure::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private $measures;

    public function __construct(
        ?int $position = null,
        ?string $title = null,
        ?string $subtitle = null,
        ?string $content = null,
        bool $isExpanded = false,
        ?Approach $approach = null,
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
