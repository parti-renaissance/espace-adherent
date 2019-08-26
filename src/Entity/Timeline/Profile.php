<?php

namespace AppBundle\Entity\Timeline;

use A2lix\I18nDoctrineBundle\Doctrine\ORM\Util\Translatable;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\AbstractTranslatableEntity;
use AppBundle\Entity\AlgoliaIndexedEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="timeline_profiles")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Timeline\ProfileRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Profile extends AbstractTranslatableEntity implements AlgoliaIndexedEntityInterface
{
    use Translatable;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
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
        /** @var ProfileTranslation $translation */
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
