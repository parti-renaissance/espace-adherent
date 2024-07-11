<?php

namespace App\Entity;

use App\Repository\SocialShareRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'social_shares')]
#[ORM\Entity(repositoryClass: SocialShareRepository::class)]
class SocialShare
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

    #[ORM\Column(type: 'bigint')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(length: 100)]
    #[Assert\Length(max: 100)]
    private $name = '';

    #[ORM\Column]
    #[Gedmo\Slug(fields: ['name'])]
    private $slug;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    #[Assert\Choice(['image', 'video', 'pdf'])]
    private $type = self::TYPE_IMAGE;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    private $description;

    #[ORM\Column]
    #[Assert\Url]
    #[Assert\Length(max: 255)]
    private $defaultUrl = '';

    #[ORM\Column(nullable: true)]
    #[Assert\Url]
    #[Assert\Length(max: 255)]
    private $facebookUrl = '';

    #[ORM\Column(nullable: true)]
    #[Assert\Url]
    #[Assert\Length(max: 255)]
    private $twitterUrl = '';

    #[ORM\ManyToOne(targetEntity: SocialShareCategory::class)]
    #[Assert\NotBlank]
    private $socialShareCategory;

    #[ORM\ManyToOne(targetEntity: Media::class, cascade: ['persist'])]
    #[Assert\NotBlank]
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
        return (string) $this->name;
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
