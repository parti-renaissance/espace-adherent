<?php

namespace AppBundle\Entity\Timeline;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Media;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use AppBundle\Entity\EntityMediaInterface;
use AppBundle\Entity\EntityMediaTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="timeline_themes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Timeline\ThemeRepository")
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
     * @var ThemeMeasure[]|Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Timeline\ThemeMeasure", mappedBy="theme", cascade={"all"})
     */
    private $measures;

    public function __construct()
    {
        $this->measures = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): string
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

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getFeatured(): bool
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

    public function addMeasure(ThemeMeasure $measure): void
    {
        if (!$this->measures->contains($measure)) {
            $measure->setTheme($this);
            $this->measures->add($measure);
        }
    }

    public function removeMeasure(ThemeMeasure $measure): void
    {
        $this->measures->removeElement($measure);
    }

    public function setFeaturedMeasure(Measure $measure): void
    {
        foreach ($this->measures as $themeMeasure) {
            if ($themeMeasure->getMeasure()->equals($measure)) {
                $themeMeasure->setFeatured(true);

                return;
            }
        }

        throw new \InvalidArgumentException(sprintf(
           'Theme "%s" has no measure "%s".',
           $this->title,
           $measure->getTitle()
        ));
    }

    /**
     * @Algolia\Attribute
     */
    public function measures(): array
    {
        $measures = [];

        foreach ($this->measures as $themeMeasure) {
            $measure = $themeMeasure->getMeasure();

            $profiles = [];
            foreach ($measure->getProfiles() as $profile) {
                $profiles[] = [
                    'title' => $profile->getTitle(),
                    'slug' => $profile->getSlug(),
                    'description' => $profile->getDescription(),
                ];
            }

            $measures[] = [
                'id' => $measure->getId(),
                'title' => $measure->getTitle(),
                'status' => $measure->getStatus(),
                'featured' => $themeMeasure->isFeatured(),
                'global' => $measure->isGlobal(),
                'profiles' => $profiles,
                'updated' => $measure->getUpdatedAt(),
                'link' => $measure->getLink(),
            ];
        }

        return $measures;
    }

    /**
     * @Algolia\Attribute
     */
    public function image(): ?string
    {
        return $this->media ? $this->media->getPathWithDirectory() : null;
    }

    public static function create(
        string $title,
        string $slug,
        string $description,
        Media $media,
        bool $isFeatured
    ): self {
        $theme = new self();

        $theme->title = $title;
        $theme->slug = $slug;
        $theme->description = $description;
        $theme->media = $media;
        $theme->featured = $isFeatured;

        return $theme;
    }
}
