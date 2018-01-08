<?php

namespace AppBundle\Entity\Timeline;

use A2lix\I18nDoctrineBundle\Doctrine\ORM\Util\Translatable;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="timeline_profiles")
 * @ORM\Entity
 */
class Profile
{
    use Translatable;

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
     * @Assert\Valid
     */
    protected $translations;

    public function __toString()
    {
        if ($translation = $this->getTranslation('fr')) {
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
        foreach (['fr', 'en'] as $locale) {
            /* @var $translation ThemeTranslation */
            if ($translation = $this->getTranslation($locale)) {
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
        foreach (['fr', 'en'] as $locale) {
            /* @var $translation ThemeTranslation */
            if ($translation = $this->getTranslation($locale)) {
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
        foreach (['fr', 'en'] as $locale) {
            /* @var $translation ThemeTranslation */
            if ($translation = $this->getTranslation($locale)) {
                $descriptions[$locale] = $translation->getDescription();
            }
        }

        return $descriptions ?? [];
    }

    private function getTranslation(string $locale): ?ProfileTranslation
    {
        $translation = $this->translations->filter(function (ProfileTranslation $translation) use ($locale) {
            return $locale === $translation->getLocale();
        })->first();

        return $translation ? $translation : null;
    }
}
