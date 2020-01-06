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
 * @ORM\Table(name="programmatic_foundation_project")
 *
 * @UniqueEntity(
 *     fields={"position", "measure"},
 *     errorPath="position",
 *     message="programmatic_foundation.unique_position.project"
 * )
 *
 * @Algolia\Index(autoIndex=false)
 */
class Project
{
    use EntityIdentityTrait;
    use TimestampableEntity;

    private const CITY_SMALL = 'Petite commune';
    private const CITY_MEDIUM = 'Ville moyenne';
    private const CITY_LARGE = 'Métropole';
    private const CITY_OTHER = 'Autre';

    public const CITY_TYPES = [
        self::CITY_SMALL,
        self::CITY_MEDIUM,
        self::CITY_LARGE,
        self::CITY_OTHER,
    ];

    /**
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThan(value=0, message="programmatic_foundation.position.greater_than_zero")
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $position;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank(message="programmatic_foundation.title.not_empty")
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(message="programmatic_foundation.content.not_empty")
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $content;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank(message="programmatic_foundation.city.not_empty")
     * @Assert\Choice(choices=Project::CITY_TYPES)
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $city;

    /**
     * @ORM\Column(type="boolean")
     * @SymfonySerializer\Groups({"approach_list_read"})
     */
    private $isExpanded;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ProgrammaticFoundation\Measure", inversedBy="projects")
     *
     * @Assert\NotNull(message="programmatic_foundation.parent.required.project")
     */
    private $measure;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ProgrammaticFoundation\Tag")
     * @ORM\JoinTable(name="programmatic_foundation_project_tag")
     * @SymfonySerializer\Groups({"approach_list_read"})
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
        $this->uuid = Uuid::uuid4();
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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getIsExpanded(): bool
    {
        return $this->isExpanded;
    }

    public function setIsExpanded(bool $isExpanded): void
    {
        $this->isExpanded = $isExpanded;
    }

    public function getMeasure(): ?Measure
    {
        return $this->measure;
    }

    public function setMeasure(?Measure $measure): void
    {
        $this->measure = $measure;
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
