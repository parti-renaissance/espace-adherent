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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="programmatic_foundation_approach")
 * @UniqueEntity("position", message="programmatic_foundation.unique_position.approach")
 * @Algolia\Index(autoIndex=false)
 */
class Approach
{
    use EntityIdentityTrait;
    use TimestampableEntity;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThan(value=0, message="programmatic_foundation.position.greater_than_zero")
     */
    private $position;

    /**
     * @ORM\Column
     * @Assert\NotBlank(message="programmatic_foundation.title.not_empty")
     */
    private $title;

    /**
     * @ORM\Column(nullable=true)
     */
    private $content;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\ProgrammaticFoundation\SubApproach",
     *     mappedBy="approach",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"position": "ASC"})
     * @Assert\Valid
     */
    private $subApproaches;

    public function __construct(int $position = null, string $title = null, string $content = null)
    {
        $this->uuid = Uuid::uuid4();
        $this->position = $position;
        $this->title = $title;
        $this->content = $content;
        $this->subApproaches = new ArrayCollection();
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

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getSubApproaches(): Collection
    {
        return $this->subApproaches;
    }

    public function setSubApproaches(iterable $subApproaches): void
    {
        foreach ($subApproaches as $subApproach) {
            $this->addSubApproach($subApproach);
        }
    }

    public function addSubApproach(SubApproach $subApproach): void
    {
        if (!$this->subApproaches->contains($subApproach)) {
            $this->subApproaches->add($subApproach);
            $subApproach->setApproach($this);
        }
    }

    public function removeSubApproach(SubApproach $subApproach): void
    {
        if ($this->approaches->contains($subApproach)) {
            $this->approaches->removeElement($subApproach);
            if ($subApproach->getApproach() === $this) {
                $subApproach->setApproach(null);
            }
        }
    }
}
