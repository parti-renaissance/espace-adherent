<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="pages")
 * @ORM\Entity(repositoryClass="App\Repository\PageRepository")
 *
 * @UniqueEntity(fields={"slug"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Page implements EntityMediaInterface, EntityContentInterface, EntitySoftDeletedInterface
{
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;
    use EntityContentTrait;
    use EntityMediaTrait;

    public const LAYOUT_DEFAULT = 'default';
    public const LAYOUT_DEFAULT_WITH_HEADER_IMAGE = 'default_with_header_image';
    public const LAYOUT_MUNICIPALES = 'municipales';
    public const LAYOUT_RENAISSANCE = 'renaissance';

    public const LAYOUTS = [
        self::LAYOUT_DEFAULT,
        self::LAYOUT_DEFAULT_WITH_HEADER_IMAGE,
        self::LAYOUT_MUNICIPALES,
        self::LAYOUT_RENAISSANCE,
    ];

    /**
     * @var int|null
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=100)
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(options={"default": "default"})
     *
     * @Assert\Choice(choices=Page::LAYOUTS)
     */
    private $layout = self::LAYOUT_DEFAULT;

    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Media", cascade={"persist"})
     */
    private $headerMedia;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }

    public function getHeaderMedia(): ?Media
    {
        return $this->headerMedia;
    }

    public function setHeaderMedia(?Media $headerMedia = null): void
    {
        $this->headerMedia = $headerMedia;
    }
}
