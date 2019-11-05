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
 * @ORM\Table(name="programmatic_foundation_project")
 * @UniqueEntity(
 *     fields={"position", "measure"},
 *     errorPath="position",
 *     message="L'ordre d'affichage doit être unique pour une mesure donnée"
 * )
 */
class Project
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThan(value=0, message="La position doit être supérieure à 0")
     */
    private $position;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank(message="Le titre ne peut pas être vide")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(message="Le contenu ne peut pas être vide")
     */
    private $content;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank(message="Le ville ne peut pas être vide")
     */
    private $city;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isExpanded = false;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ProgrammaticFoundation\Measure", inversedBy="projects")
     *
     * @Assert\NotNull(message="Un projet doit être lié à une mesure")
     */
    private $measure;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ProgrammaticFoundation\Tag")
     * @ORM\JoinTable(name="programmatic_foundation_project_tag")
     */
    private $tags;

    public function __construct(
        int $position = null,
        string $title = null,
        string $content = null,
        string $city = null,
        bool $isExpanded = false,
        Measure $measure = null
    ) {
        $this->position = $position;
        $this->title = $title;
        $this->content = $content;
        $this->city = $city;
        $this->isExpanded = $isExpanded;
        $this->measure = $measure;
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

    public function getTitle(): string
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

    public function setContent(string $content): self
    {
        $this->content = $content;

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

    public function getMeasure(): ?Measure
    {
        return $this->measure;
    }

    public function setMeasure(?Measure $measure): self
    {
        $this->measure = $measure;

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
}
