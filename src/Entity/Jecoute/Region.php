<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Entity\EntityNameSlugTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get": {
 *             "path": "/jecoute/regions",
 *             "method": "GET",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "uuid",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "Filter Regions by exact uuid.",
 *                         "example": "a046adbe-9c7b-56a9-a676-6151a6785dda",
 *                     },
 *                     {
 *                         "name": "name",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "Filter Regions by partial name.",
 *                         "example": "Normandie",
 *                     },
 *                     {
 *                         "name": "code",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "Filter Regions by exact code.",
 *                         "example": "28",
 *                     },
 *                 }
 *             }
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "method": "GET",
 *             "path": "/jecoute/regions/{id}",
 *             "swagger_context": {
 *                 "summary": "Retrieves a Region resource by UUID.",
 *                 "description": "Retrieves a Region resource by UUID.",
 *                 "parameters": {
 *                     {
 *                         "name": "code",
 *                         "in": "path",
 *                         "type": "string",
 *                         "description": "The code of the Region resource.",
 *                         "example": "28",
 *                     }
 *                 }
 *             }
 *         }
 *     },
 *     attributes={
 *         "normalization_context": {"groups": {"jecoute_region_read"}},
 *         "access_control": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')",
 *         "order": {"code": "ASC"},
 *     },
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "uuid": "exact",
 *     "name": "partial",
 *     "code": "partial",
 * })
 * @ApiFilter(OrderFilter::class, properties={"code"})
 *
 * @ORM\Table(name="jecoute_region")
 * @ORM\Entity
 *
 * @UniqueEntity(fields={"code"})
 */
class Region
{
    use EntityTimestampableTrait;
    use EntityNameSlugTrait;

    private const IMAGES_DIRECTORY = 'files/jemarche/regions';

    /**
     * @var int|null
     *
     * @ApiProperty(identifier=false)
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     *
     * @SymfonySerializer\Groups({"jecoute_region_read"})
     */
    protected $uuid;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\Length(max=120)
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"jecoute_region_read"})
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ApiProperty(identifier=true)
     *
     * @ORM\Column(unique=true)
     *
     * @Assert\Length(max=10)
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"jecoute_region_read"})
     */
    protected $code;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\Length(max=120)
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"jecoute_region_read"})
     */
    protected $subtitle;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"jecoute_region_read"})
     */
    private $description;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\Choice(callback={"App\Jecoute\RegionColorEnum", "all"})
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"jecoute_region_read"})
     */
    protected $primaryColor;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     *
     * @SymfonySerializer\Groups({"jecoute_region_read"})
     */
    protected $externalLink;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $banner;

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={
     *         "image/*"
     *     }
     * )
     */
    private $bannerFile;

    private $removeBanner = false;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $logo;

    /**
     * @var UploadedFile|null
     *
     * @Assert\File(
     *     maxSize="5M",
     *     mimeTypes={
     *         "image/*"
     *     }
     * )
     */
    private $logoFile;

    public function __construct(
        UuidInterface $uuid = null,
        string $name = null,
        string $code = null,
        string $subtitle = null,
        string $description = null,
        string $primaryColor = null,
        string $logo = null,
        string $banner = null,
        string $externalLink = null
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();

        if ($name) {
            $this->setName($name);
        }

        $this->code = $code;
        $this->subtitle = $subtitle;
        $this->description = $description;
        $this->primaryColor = $primaryColor;
        $this->logo = $logo;
        $this->banner = $banner;
        $this->externalLink = $externalLink;
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->name, $this->code);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
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

    public function getRemoveBanner(): bool
    {
        return $this->removeBanner;
    }

    public function setRemoveBanner(bool $removeBanner): void
    {
        $this->removeBanner = $removeBanner;
    }

    public function getBannerPathWithDirectory(): string
    {
        return sprintf('%s/%s', self::IMAGES_DIRECTORY, $this->banner);
    }

    public function setBannerFromUploadedFile(): void
    {
        $this->banner = sprintf('%s-banner.%s',
            md5(sprintf('%s@%s', $this->getUuid(), $this->bannerFile->getClientOriginalName())),
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
        return sprintf('%s/%s', self::IMAGES_DIRECTORY, $this->logo);
    }

    public function setLogoFromUploadedFile(): void
    {
        $this->logo = sprintf('%s-logo.%s',
            md5(sprintf('%s@%s', $this->getUuid(), $this->logoFile->getClientOriginalName())),
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
}
