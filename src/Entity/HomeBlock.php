<?php

namespace App\Entity;

use App\Repository\HomeBlockRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HomeBlockRepository::class)]
#[ORM\Table(name: 'home_blocks')]
#[UniqueEntity(fields: ['position'])]
#[UniqueEntity(fields: ['positionName'])]
class HomeBlock
{
    public const TYPE_ARTICLE = 'article';
    public const TYPE_VIDEO = 'video';
    public const TYPE_BANNER = 'banner';

    /**
     * @var int
     */
    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var int
     */
    #[ORM\Column(type: 'smallint', unique: true)]
    private $position;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 30)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 30, unique: true)]
    private $positionName;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 70)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 70)]
    private $title;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, nullable: true)]
    private $subtitle;

    /**
     * @var string
     */
    #[Assert\Choice(['video', 'article', 'banner'])]
    #[Assert\NotBlank]
    #[ORM\Column(length: 10)]
    private $type = self::TYPE_ARTICLE;

    /**
     * @var Media|null
     */
    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: Media::class)]
    private $media;

    /**
     * @var string|null
     */
    #[Assert\NotBlank]
    #[ORM\Column]
    private $link;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $displayFilter = true;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $displayTitles = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $displayBlock = true;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $videoControls = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $videoAutoplayLoop = true;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 70)]
    #[ORM\Column(length: 70, nullable: true)]
    private $titleCta;

    /**
     * @var string|null
     */
    #[Assert\Choice(callback: ['\App\Admin\Color', 'all'])]
    #[ORM\Column(length: 6, nullable: true)]
    private $colorCta;

    /**
     * @var string|null
     */
    #[Assert\Choice(callback: ['\App\Admin\Color', 'all'])]
    #[ORM\Column(length: 6, nullable: true)]
    private $bgColor;

    /**
     * @var \DateTime
     */
    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private $updatedAt;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $forRenaissance = false;

    public function __toString()
    {
        return $this->positionName ?: '';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPositionName(): ?string
    {
        return $this->positionName;
    }

    public function setPositionName(?string $positionName): void
    {
        $this->positionName = $positionName;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): void
    {
        $this->media = $media;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    public function hasDisplayFilter(): bool
    {
        return $this->displayFilter;
    }

    public function setDisplayFilter(bool $displayFilter): void
    {
        $this->displayFilter = $displayFilter;
    }

    public function hasDisplayTitles(): bool
    {
        return $this->displayTitles;
    }

    public function setDisplayTitles(bool $displayTitles): void
    {
        $this->displayTitles = $displayTitles;
    }

    public function isDisplayBlock(): bool
    {
        return $this->displayBlock;
    }

    public function setDisplayBlock(bool $displayBlock): void
    {
        $this->displayBlock = $displayBlock;
    }

    public function hasVideoControls(): bool
    {
        return $this->videoControls;
    }

    public function setVideoControls(bool $videoControls): void
    {
        $this->videoControls = $videoControls;
    }

    public function hasVideoAutoplayLoop(): bool
    {
        return $this->videoAutoplayLoop;
    }

    public function setVideoAutoplayLoop(bool $videoAutoplayLoop): void
    {
        $this->videoAutoplayLoop = $videoAutoplayLoop;
    }

    public function getTitleCta(): ?string
    {
        return $this->titleCta;
    }

    public function setTitleCta(?string $titleCta): void
    {
        $this->titleCta = $titleCta;
    }

    public function getColorCta(): ?string
    {
        return $this->colorCta;
    }

    public function setColorCta(?string $colorCta): void
    {
        $this->colorCta = $colorCta;
    }

    public function getBgColor(): ?string
    {
        return $this->bgColor;
    }

    public function setBgColor(?string $bgColor): void
    {
        $this->bgColor = $bgColor;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function isForRenaissance(): ?bool
    {
        return $this->forRenaissance;
    }

    public function setForRenaissance(bool $forRenaissance): void
    {
        $this->forRenaissance = $forRenaissance;
    }
}
