<?php

namespace AppBundle\Entity\Timeline;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\EntityMediaInterface;
use AppBundle\Entity\EntityMediaTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="timeline_themes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Timeline\ThemeRepository")
 *
 * @Algolia\Index(
 *     hitsPerPage=100,
 *     attributesForFaceting={"title", "profileIds"}
 * )
 */
class Theme implements EntityMediaInterface
{
    use EntityMediaTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Algolia\Attribute
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     *
     * @Algolia\Attribute
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     *
     * @Algolia\Attribute
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     *
     * @Algolia\Attribute
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Algolia\Attribute
     */
    private $featured = false;

    /**
     * @var Measure[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Timeline\Measure", mappedBy="themes")
     */
    private $measures;

    public function __construct(
        string $title = null,
        string $slug = null,
        string $description = null,
        bool $featured = false
    ) {
        $this->title = $title;
        $this->slug = $slug;
        $this->description = $description;
        $this->featured = $featured;
        $this->measures = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->title ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): void
    {
        $this->featured = $featured;
    }

    public function getMeasures(): Collection
    {
        return $this->measures;
    }

    public function addMeasure(Measure $measure): void
    {
        if (!$this->measures->contains($measure)) {
            $this->measures->add($measure);
        }
    }

    public function removeMeasure(Measure $measure): void
    {
        $this->measures->removeElement($measure);
    }

    /**
     * @Algolia\Attribute
     */
    public function image(): ?string
    {
        return $this->media ? $this->media->getPathWithDirectory() : null;
    }

    /**
     * @Algolia\Attribute
     */
    public function measureIds(): array
    {
        return array_map(function (Measure $measure) {
            return $measure->getId();
        }, $this->measures->toArray());
    }

    /**
     * @Algolia\Attribute
     */
    public function profileIds(): array
    {
        $profiles = new ArrayCollection();

        foreach ($this->measures as $measure) {
            foreach ($measure->getProfiles() as $profile) {
                if (!$profiles->contains($profile)) {
                    $profiles->add($profile);
                }
            }
        }

        return array_map(function (Profile $profile) {
            return $profile->getId();
        }, $profiles->toArray());
    }
}
