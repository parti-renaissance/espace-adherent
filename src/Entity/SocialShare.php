<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'social_shares')]
class SocialShare implements \Stringable
{
    use EntityTimestampableTrait;
    use PositionTrait;

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_PDF = 'pdf';

    public const TYPES = [
        self::TYPE_IMAGE,
        self::TYPE_VIDEO,
        self::TYPE_PDF,
    ];

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100)]
    private $name = '';

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column]
    private $slug;

    #[Assert\Choice(['image', 'video', 'pdf'])]
    #[Assert\NotBlank]
    #[ORM\Column(length: 10)]
    private $type = self::TYPE_IMAGE;

    #[Assert\Length(max: 200)]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    private $description;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column]
    private $defaultUrl = '';

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(nullable: true)]
    private $facebookUrl = '';

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(nullable: true)]
    private $twitterUrl = '';

    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: SocialShareCategory::class)]
    private $socialShareCategory;

    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: Media::class, cascade: ['persist'])]
    private $media;

    #[ORM\Column(type: 'boolean')]
    private $published;

    public function __construct(string $name = '', int $position = 1, $published = false)
    {
        $this->name = $name;
        $this->position = $position;
        $this->published = $published;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = (string) $name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;
    }

    public function getDefaultUrl(): ?string
    {
        return $this->defaultUrl;
    }

    public function setDefaultUrl(?string $defaultUrl)
    {
        $this->defaultUrl = $defaultUrl ?: '';
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl)
    {
        $this->facebookUrl = $facebookUrl ?: '';
    }

    public function getTwitterUrl(): ?string
    {
        return $this->twitterUrl;
    }

    public function setTwitterUrl(?string $twitterUrl)
    {
        $this->twitterUrl = $twitterUrl ?: '';
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type)
    {
        $this->type = $type;
    }

    public function getSocialShareCategory(): ?SocialShareCategory
    {
        return $this->socialShareCategory;
    }

    public function setSocialShareCategory(?SocialShareCategory $socialShareCategory)
    {
        $this->socialShareCategory = $socialShareCategory;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media)
    {
        $this->media = $media;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(?bool $published)
    {
        $this->published = $published;
    }
}
