<?php

namespace App\Entity\Timeline;

use App\Entity\AbstractTranslatableEntity;
use App\Entity\AlgoliaIndexedEntityInterface;
use App\Repository\Timeline\ProfileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfileRepository::class)]
#[ORM\Table(name: 'timeline_profiles')]
class Profile extends AbstractTranslatableEntity implements AlgoliaIndexedEntityInterface
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
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

    public function getTitles(): array
    {
        return $this->getFieldTranslations('title');
    }

    public function getSlugs(): array
    {
        return $this->getFieldTranslations('slug');
    }

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

    public function getIndexOptions(): array
    {
        return [];
    }
}
