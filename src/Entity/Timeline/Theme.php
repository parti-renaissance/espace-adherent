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
     * @Algolia\Attribute(algoliaName="image")
     */
    public function getImage(): ?string
    {
        return $this->media ? $this->media->getPathWithDirectory() : null;
    }

    /**
     * @Algolia\Attribute(algoliaName="measureIds")
     */
    public function getMeasureIds(): array
    {
        return array_map(function (Measure $measure) {
            return $measure->getId();
        }, $this->measures->toArray());
    }

    /**
     * @Algolia\Attribute(algoliaName="measureTitles")
     */
    public function getMeasureTitles(): array
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
     * @Algolia\Attribute(algoliaName="profileIds")
     */
    public function getProfileIds(): array
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
     * @Algolia\Attribute(algoliaName="manifestoIds")
     */
    public function getManifestoIds(): array
    {
        return array_values(array_unique(
            array_map(function (Measure $measure) {
                return $measure->getManifesto()->getId();
            }, $this->measures->toArray())
        ));
    }

    /**
     * @Algolia\Attribute(algoliaName="titles")
     */
    public function getTitles(): array
    {
        return $this->getFieldTranslations('title');
    }

    /**
     * @Algolia\Attribute(algoliaName="slugs")
     */
    public function getSlugs(): array
    {
        return $this->getFieldTranslations('slug');
    }

    /**
     * @Algolia\Attribute(algoliaName="descriptions")
     */
    public function getDescriptions(): array
    {
        return $this->getFieldTranslations('description');
    }

    public function exportTitles(): string
    {
        return implode(', ', $this->getTitles());
    }

    public function exportSlugs(): string
    {
        return implode(', ', $this->getSlugs());
    }

    public function exportDescriptions(): string
    {
        return implode(', ', $this->getDescriptions());
    }
}
