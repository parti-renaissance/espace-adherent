<?php

namespace AppBundle\Entity\Timeline;

use A2lix\I18nDoctrineBundle\Doctrine\ORM\Util\Translatable;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="timeline_profiles")
 * @ORM\Entity
 */
class Profile
{
    use Translatable;

    public const DEFAULT_LOCALE = 'fr';
    public const LOCALES_TO_INDEX = ['fr', 'en'];

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
     * @var ProfileTranslation[]|Collection
     *
     * @Assert\Valid
     */
    private $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function __toString()
    {
        if ($translation = $this->getTranslation(self::DEFAULT_LOCALE)) {
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
        foreach (self::LOCALES_TO_INDEX as $locale) {
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
        foreach (self::LOCALES_TO_INDEX as $locale) {
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
        foreach (self::LOCALES_TO_INDEX as $locale) {
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

        return $translation ? : null;
    }
}
