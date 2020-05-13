<?php

namespace App\Entity\Timeline;

use A2lix\I18nDoctrineBundle\Doctrine\ORM\Util\Translation;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityTranslationInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="timeline_profile_translations")
 *
 * @UniqueEntity(fields={"locale", "title"}, errorPath="title")
 * @UniqueEntity(fields={"locale", "slug"}, errorPath="slug")
 */
class ProfileTranslation implements EntityTranslationInterface
{
    use Translation;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     *
     * @Algolia\Attribute
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     *
     * @Algolia\Attribute
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     *
     * @Algolia\Attribute
     */
    private $description;

    public function __construct(
        string $locale = null,
        string $title = null,
        string $slug = null,
        string $description = null
    ) {
        $this->locale = $locale;
        $this->title = $title;
        $this->slug = $slug;
        $this->description = $description;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isEmpty(): bool
    {
        return empty($this->title) && empty($this->slug) && empty($this->description);
    }
}
