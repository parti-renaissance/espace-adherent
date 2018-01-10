<?php

namespace AppBundle\Entity\Timeline;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityTranslatableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="timeline_profiles")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class Profile
{
    use EntityTranslatableTrait;

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

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function __toString()
    {
        /* @var $translation ProfileTranslation */
        if ($translation = $this->translate()) {
            return $translation->getTitle();
        }

        return '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Algolia\Attribute
     */
    public function titles(): array
    {
        foreach ($this->getLocales() as $locale) {
            /* @var $translation ProfileTranslation */
            if ($translation = $this->translate($locale)) {
                $titles[$locale] = $translation->getTitle();
            }
        }

        return $titles ?? [];
    }

    /**
     * @Algolia\Attribute
     */
    public function slugs(): array
    {
        foreach ($this->getLocales() as $locale) {
            /* @var $translation ProfileTranslation */
            if ($translation = $this->translate($locale)) {
                $slugs[$locale] = $translation->getSlug();
            }
        }

        return $slugs ?? [];
    }

    /**
     * @Algolia\Attribute
     */
    public function descriptions(): array
    {
        foreach ($this->getLocales() as $locale) {
            /* @var $translation ProfileTranslation */
            if ($translation = $this->translate($locale)) {
                $descriptions[$locale] = $translation->getDescription();
            }
        }

        return $descriptions ?? [];
    }
}
