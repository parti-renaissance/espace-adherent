<?php

namespace AppBundle\Entity\Timeline;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityTranslatableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="timeline_profiles")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Timeline\ProfileRepository")
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
        /* @var $french ProfileTranslation */
        if (!$french = $this->translate('fr')) {
            return [];
        }

        /* @var $english ProfileTranslation */
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
        /* @var $french ProfileTranslation */
        if (!$french = $this->translate('fr')) {
            return [];
        }

        /* @var $english ProfileTranslation */
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
        /* @var $french ProfileTranslation */
        if (!$french = $this->translate('fr')) {
            return [];
        }

        /* @var $english ProfileTranslation */
        if (!$english = $this->translate('en')) {
            $english = $french;
        }

        return [
            'fr' => $french->getDescription(),
            'en' => $english->getDescription(),
        ];
    }
}
