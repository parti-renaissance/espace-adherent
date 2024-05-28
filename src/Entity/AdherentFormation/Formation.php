<?php

namespace App\Entity\AdherentFormation;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\ScopeVisibilityFilter;
use App\Entity\Adherent;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityScopeVisibilityTrait;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\EntityTimestampableTrait;
use App\Entity\PositionTrait;
use App\Validator\AdherentFormation\FormationContent;
use App\Validator\Scope\ScopeVisibility;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     routePrefix="/v3",
 *     attributes={
 *         "order": {"createdAt": "DESC"},
 *         "normalization_context": {
 *             "groups": {"formation_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"formation_write"},
 *         },
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'adherent_formations')",
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/formations",
 *             "normalization_context": {
 *                 "groups": {"formation_list_read"}
 *             },
 *             "maximum_items_per_page": 1000
 *         },
 *         "post": {
 *             "path": "/formations",
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/formations/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'adherent_formations') and is_granted('SCOPE_CAN_MANAGE', object)",
 *         },
 *         "put": {
 *             "path": "/formations/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'adherent_formations') and is_granted('SCOPE_CAN_MANAGE', object)",
 *         },
 *         "post_file": {
 *             "path": "/formations/{uuid}/file",
 *             "method": "POST",
 *             "controller": "App\Controller\Api\FormationUploadFileController",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'adherent_formations') and is_granted('SCOPE_CAN_MANAGE', object)",
 *         },
 *         "get_file": {
 *             "path": "/formations/{uuid}/file",
 *             "method": "GET",
 *             "controller": "App\Controller\Api\FormationDownloadFileController",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'adherent_formations') and is_granted('SCOPE_CAN_MANAGE', object)",
 *         },
 *         "delete": {
 *             "path": "/formations/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'adherent_formations') and is_granted('SCOPE_CAN_MANAGE', object)",
 *         },
 *     }
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "title": "partial",
 *     "visibility": "exact",
 * })
 *
 * @ApiFilter(ScopeVisibilityFilter::class)
 *
 * @ORM\Entity(repositoryClass="App\Repository\AdherentFormation\FormationRepository");
 * @ORM\Table(name="adherent_formation")
 * @ORM\EntityListeners({"App\EntityListener\AdherentFormationListener"})
 *
 * @UniqueEntity(fields={"zone", "title"}, message="adherent_formation.zone_title.unique_entity")
 *
 * @ScopeVisibility
 * @FormationContent
 */
class Formation implements EntityScopeVisibilityWithZoneInterface, EntityAdherentBlameableInterface, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;
    use EntityScopeVisibilityTrait;
    use PositionTrait;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank(message="Veuillez renseigner un titre.")
     * @Assert\Length(allowEmptyString=true, min=2, minMessage="Le titre doit faire au moins 2 caractères.")
     */
    #[Groups(['formation_read', 'formation_list_read', 'formation_write'])]
    private ?string $title = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(allowEmptyString=true, min=2, minMessage="La description doit faire au moins 2 caractères.")
     */
    #[Groups(['formation_read', 'formation_list_read', 'formation_write'])]
    private ?string $description = null;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices=FormationContentTypeEnum::ALL)
     */
    #[Groups(['formation_read', 'formation_list_read', 'formation_write'])]
    private string $contentType = FormationContentTypeEnum::FILE;

    /**
     * @Assert\File(
     *     maxSize="5M",
     *     binaryFormat=false,
     *     mimeTypes={
     *         "image/*",
     *         "video/mpeg",
     *         "video/mp4",
     *         "video/quicktime",
     *         "video/webm",
     *         "application/pdf",
     *         "application/x-pdf",
     *         "application/vnd.ms-powerpoint",
     *         "application/vnd.openxmlformats-officedocument.presentationml.presentation",
     *         "application/msword",
     *         "application/vnd.ms-excel",
     *         "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *         "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *         "application/rtf",
     *         "text/plain",
     *         "text/csv",
     *         "text/html",
     *         "text/calendar"
     *     }
     * )
     */
    private ?UploadedFile $file = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $filePath = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     */
    #[Groups(['formation_read', 'formation_list_read', 'formation_write'])]
    private ?string $link = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    #[Groups(['formation_read', 'formation_list_read', 'formation_write'])]
    private bool $published = false;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    #[Groups(['formation_read', 'formation_list_read'])]
    private int $printCount = 0;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $valid = false;

    /**
     * @var Collection|Adherent[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Adherent", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="adherent_formation_print_by_adherents", joinColumns={@ORM\JoinColumn(onDelete="CASCADE")})
     */
    private Collection $printByAdherents;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->printByAdherents = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function isFileContent(): bool
    {
        return FormationContentTypeEnum::FILE === $this->contentType;
    }

    public function isLinkContent(): bool
    {
        return FormationContentTypeEnum::LINK === $this->contentType;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function hasFilePath(): bool
    {
        return null !== $this->filePath;
    }

    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): void
    {
        $this->link = $link;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getPrintCount(): int
    {
        return $this->printCount;
    }

    public function incrementPrintCount(): void
    {
        ++$this->printCount;
    }

    /**
     * @return Collection|Adherent[]
     */
    public function getPrintByAdherents(): Collection
    {
        return $this->printByAdherents;
    }

    public function setPrintByAdherents(Collection $printByAdherents): void
    {
        $this->printByAdherents = $printByAdherents;
    }

    public function addPrintByAdherent(Adherent $adherent): bool
    {
        if (!$this->printByAdherents->contains($adherent)) {
            $this->printByAdherents->add($adherent);
            $this->incrementPrintCount();

            return true;
        }

        return false;
    }
}
