<?php

namespace AppBundle\Entity\ProgrammaticFoundation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="programmatic_foundation_sub_approach")
 * @UniqueEntity(
 *     fields={"position", "approach"},
 *     errorPath="position",
 *     message="L'ordre d'affichage doit être unique pour un grand axe donné"
 * )
 */
class SubApproach
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
     * @ORM\Column(nullable=true)
     */
    private $subtitle;

    /**
     * @ORM\Column(nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isExpanded = false;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ProgrammaticFoundation\Approach", inversedBy="subApproaches")
     * @Assert\NotNull(message="Un axe secondaire doit être lié à un grand axe")
     */
    private $approach;

    /**
     * @ORM\OneToMany(
     *   targetEntity="AppBundle\Entity\ProgrammaticFoundation\Measure",
     *   mappedBy="approach",
     *   indexBy="id",
     *   cascade={"all"},
     *   orphanRemoval=true
     * )
     * @Assert\Valid
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

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

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

    public function getApproach(): ?Approach
    {
        return $this->approach;
    }

    public function setApproach(?Approach $approach): self
    {
        $this->approach = $approach;

        return $this;
    }

    public function getMeasures(): Collection
    {
        return $this->measures;
    }

    public function setMeasures(iterable $measures): self
    {
        foreach ($measures as $measure) {
            $this->addMeasure($measure);
        }

        return $this;
    }

    public function addMeasure(Measure $measure): self
    {
        if (!$this->measures->contains($measure)) {
            $this->measures->add($measure);
            $measure->setApproach($this);
        }

        return $this;
    }

    public function removeMeasure(Measure $measure): self
    {
        if ($this->measures->contains($measure)) {
            $this->measures->removeElement($measure);
            if ($measure->getApproach() === $this) {
                $measure->setApproach(null);
            }
        }

        return $this;
    }

    public function getSectionIdentifier(): string
    {
        return sprintf('%s.%d', $this->getApproach()->getSectionIdentifier(), $this->getPosition());
    }
}
