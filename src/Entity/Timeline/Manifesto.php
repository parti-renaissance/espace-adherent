<?php

namespace App\Entity\Timeline;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\AbstractTranslatableEntity;
use App\Entity\AlgoliaIndexedEntityInterface;
use App\Entity\EntityMediaTrait;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;

/**
 * @ORM\Table(name="timeline_manifestos")
 * @ORM\Entity(repositoryClass="App\Repository\Timeline\ManifestoRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Manifesto extends AbstractTranslatableEntity implements AlgoliaIndexedEntityInterface
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

    public function __toString(): string
    {
        /** @var ProfileTranslation $translation */
        if ($translation = $this->translate()) {
            return (string) $translation->getTitle();
        }

        return '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Algolia\Attribute(algoliaName="image")
     */
    public function getImage(): ?string
    {
        return $this->media ? $this->media->getPathWithDirectory() : null;
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
