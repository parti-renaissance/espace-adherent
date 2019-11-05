<?php

namespace AppBundle\Entity\ProgrammaticFoundation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="programmatic_foundation_approach")
 */
class Approach
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
    private $content;

    /**
     * @ORM\OneToMany(
     *   targetEntity="AppBundle\Entity\ProgrammaticFoundation\SubApproach",
     *   mappedBy="approach",
     *   indexBy="id",
     *   cascade={"all"},
     *   orphanRemoval=true
     * )
     * @Assert\Valid
     */
    private $subApproaches;

    public function __construct(
        int $position = null,
        string $title = null,
        string $content = null
    ) {
        $this->position = $position;
        $this->title = $title;
        $this->content = $content;
        $this->subApproaches = new ArrayCollection();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSubApproaches(): Collection
    {
        return $this->subApproaches;
    }

    public function setSubApproaches(iterable $subApproaches): self
    {
        foreach ($subApproaches as $subApproach) {
            $this->addSubApproach($subApproach);
        }

        return $this;
    }

    public function addSubApproach(SubApproach $subApproach): self
    {
        if (!$this->subApproaches->contains($subApproach)) {
            $this->subApproaches->add($subApproach);
            $subApproach->setApproach($this);
        }

        return $this;
    }

    public function removeSubApproach(SubApproach $subApproach): self
    {
        if ($this->approaches->contains($subApproach)) {
            $this->approaches->removeElement($subApproach);
            if ($subApproach->getApproach() === $this) {
                $subApproach->setApproach(null);
            }
        }

        return $this;
    }

    public function getSectionIdentifier(): string
    {
        return sprintf('%d', $this->getPosition());
    }
}
