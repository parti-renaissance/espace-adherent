<?php

namespace AppBundle\Entity\Timeline;

use A2lix\I18nDoctrineBundle\Doctrine\ORM\Util\Translatable;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\AbstractTranslatableEntity;
use AppBundle\Entity\AlgoliaIndexedEntityInterface;
use AppBundle\Entity\EntityMediaInterface;
use AppBundle\Entity\EntityMediaTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="timeline_themes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Timeline\ThemeRepository")
 *
 * @Algolia\Index(
 *     autoIndex=false,
 *     hitsPerPage=100,
 *     attributesForFaceting={
 *         "titles.fr",
 *         "titles.en",
 *         "profileIds"
 *     }
 * )
 */
class Theme extends AbstractTranslatableEntity implements EntityMediaInterface, AlgoliaIndexedEntityInterface
{
    use EntityMediaTrait;
    use Translatable;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue
     *
     * @Algolia\Attribute
     */
    private $id;

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

    public function __construct(bool $featured = false)
    {
        $this->featured = $featured;
        $this->measures = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function __toString()
    {
        /** @var ThemeTranslation $translation */
        if ($translation = $this->translate()) {
            return $translation->getTitle();
        }

        return '';
    }

    public function getId(): ?int
    {
        return $this->id;
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
    public function measureTitles(): array
    {
        $titles = [];
        foreach ($this->measures as $measure) {
            /** @var MeasureTranslation $translation */
            foreach ($measure->getTranslations() as $translation) {
                $titles[] = $translation->getTitle();
            }
        }

        return array_unique($titles);
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

    /**
     * @Algolia\Attribute
     */
    public function titles(): array
    {
        /** @var ThemeTranslation $french */
        if (!$french = $this->translate('fr')) {
            return [];
        }

        /** @var ThemeTranslation $english */
        if (!$english = $this->translate('en')) {
            $english = $french;
        }

        return [
            'fr' => $french->getTitle(),
            'en' => $english->getTitle(),
        ];
    }

    /**
     * @Algolia\Attribute
     */
    public function slugs(): array
    {
        /** @var ThemeTranslation $french */
        if (!$french = $this->translate('fr')) {
            return [];
        }

        /** @var ThemeTranslation $english */
        if (!$english = $this->translate('en')) {
            $english = $french;
        }

        return [
            'fr' => $french->getSlug(),
            'en' => $english->getSlug(),
        ];
    }

    /**
     * @Algolia\Attribute
     */
    public function descriptions(): array
    {
        /** @var ThemeTranslation $french */
        if (!$french = $this->translate('fr')) {
            return [];
        }

        /** @var ThemeTranslation $english */
        if (!$english = $this->translate('en')) {
            $english = $french;
        }

        return [
            'fr' => $french->getDescription(),
            'en' => $english->getDescription(),
        ];
    }

    public function exportTitles(): string
    {
        return join(', ', $this->titles());
    }

    public function exportSlugs(): string
    {
        return join(', ', $this->slugs());
    }

    public function exportDescriptions(): string
    {
        return join(', ', $this->descriptions());
    }
}
