<?php

declare(strict_types=1);

namespace App\Entity\Jecoute;

use App\Entity\Adherent;
use App\Entity\EntityAdministratorTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Jecoute\RegionColorEnum;
use App\Repository\Jecoute\RegionRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
#[ORM\Table(name: 'jecoute_region')]
#[UniqueEntity(fields: ['zone'], message: 'jecoute_region.zone.not_unique')]
class Region implements \Stringable
{
    use EntityTimestampableTrait;
    use EntityAdministratorTrait;

    private const IMAGES_DIRECTORY = 'files/jemarche/regions';

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var UuidInterface
     */
    #[ORM\Column(type: 'uuid', unique: true)]
    protected $uuid;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 120)]
    #[Assert\NotBlank]
    #[Groups(['jecoute_region_read'])]
    #[ORM\Column]
    protected $subtitle;

    /**
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Groups(['jecoute_region_read'])]
    #[ORM\Column(type: 'text')]
    private $description;

    /**
     * @var string|null
     */
    #[Assert\Choice(callback: [RegionColorEnum::class, 'all'])]
    #[Assert\NotBlank]
    #[Groups(['jecoute_region_read'])]
    #[ORM\Column]
    protected $primaryColor;

    /**
     * @var string|null
     */
    #[Assert\Url]
    #[Groups(['jecoute_region_read'])]
    #[ORM\Column(nullable: true)]
    protected $externalLink;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $banner;

    /**
     * @var UploadedFile|null
     */
    #[Assert\File(maxSize: '5M', mimeTypes: ['image/*'])]
    private $bannerFile;

    private $removeBannerFile = false;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $logo;

    /**
     * @var UploadedFile|null
     */
    #[Assert\File(maxSize: '5M', mimeTypes: ['image/*'])]
    private $logoFile;

    /**
     * @var Zone|null
     */
    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\OneToOne(targetEntity: Zone::class)]
    private $zone;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $enabled;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, fetch: 'EAGER')]
    private $author;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?Zone $zone = null,
        ?string $subtitle = null,
        ?string $description = null,
        ?string $primaryColor = null,
        ?string $logo = null,
        ?string $banner = null,
        ?string $externalLink = null,
        bool $enabled = true,
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->zone = $zone;
        $this->subtitle = $subtitle;
        $this->description = $description;
        $this->primaryColor = $primaryColor;
        $this->logo = $logo;
        $this->banner = $banner;
        $this->externalLink = $externalLink;
        $this->enabled = $enabled;
    }

    public function __toString()
    {
        if ($this->zone) {
            return \sprintf('%s (%s)', $this->zone->getName(), $this->zone->getCode());
        }

        return $this->uuid->toString();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(?string $primaryColor): void
    {
        $this->primaryColor = $primaryColor;
    }

    public function hasBannerUploaded(): bool
    {
        return null !== $this->banner;
    }

    public function getBannerFile(): ?UploadedFile
    {
        return $this->bannerFile;
    }

    public function setBannerFile(?UploadedFile $file): void
    {
        $this->bannerFile = $file;
    }

    public function removeBanner(): void
    {
        $this->banner = null;
    }

    public function setBanner(string $banner): void
    {
        $this->banner = $banner;
    }

    public function getRemoveBannerFile(): bool
    {
        return $this->removeBannerFile;
    }

    public function setRemoveBannerFile(bool $removeBannerFile): void
    {
        $this->removeBannerFile = $removeBannerFile;
    }

    public function getBannerPathWithDirectory(): string
    {
        return \sprintf('%s/%s', self::IMAGES_DIRECTORY, $this->banner);
    }

    public function setBannerFromUploadedFile(): void
    {
        $this->banner = \sprintf('%s-banner.%s',
            md5(\sprintf('%s@%s', $this->getUuid(), $this->bannerFile->getClientOriginalName())),
            $this->bannerFile->getClientOriginalExtension()
        );
    }

    public function hasLogoUploaded(): bool
    {
        return null !== $this->logo;
    }

    public function getLogoFile(): ?UploadedFile
    {
        return $this->logoFile;
    }

    public function setLogoFile(?UploadedFile $file): void
    {
        $this->logoFile = $file;
    }

    public function setLogo(string $logo): void
    {
        $this->logo = $logo;
    }

    public function getLogoPathWithDirectory(): string
    {
        return \sprintf('%s/%s', self::IMAGES_DIRECTORY, $this->logo);
    }

    public function setLogoFromUploadedFile(): void
    {
        $this->logo = \sprintf('%s-logo.%s',
            md5(\sprintf('%s@%s', $this->getUuid(), $this->logoFile->getClientOriginalName())),
            $this->logoFile->getClientOriginalExtension()
        );
    }

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(?string $externalLink): void
    {
        $this->externalLink = $externalLink;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): void
    {
        $this->zone = $zone;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(?Adherent $author): void
    {
        $this->author = $author;
    }
}
