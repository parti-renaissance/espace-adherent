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
     * @ORM\GeneratedValue
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
     * @Algolia\Attribute
     */
    public function titles(): array
    {
        return $this->getFieldTranslations('title');
    }

    /**
     * @Algolia\Attribute
     */
    public function slugs(): array
    {
        return $this->getFieldTranslations('slug');
    }

    /**
     * @Algolia\Attribute
     */
    public function descriptions(): array
    {
        return $this->getFieldTranslations('description');
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
