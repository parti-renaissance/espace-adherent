<?php

namespace App\Entity\AdherentFormation;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Api\Filter\ScopeVisibilityFilter;
use App\Controller\Api\FormationDownloadFileController;
use App\Controller\Api\FormationLinkController;
use App\Controller\Api\FormationUploadFileController;
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
use App\EntityListener\AdherentFormationListener;
use App\Repository\AdherentFormation\FormationRepository;
use App\Validator\AdherentFormation\FormationContent;
use App\Validator\Scope\ScopeVisibility;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: SearchFilter::class, properties: ['title' => 'partial', 'visibility' => 'exact'])]
#[ApiFilter(filterClass: ScopeVisibilityFilter::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/formations/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'adherent_formations') and is_granted('SCOPE_CAN_MANAGE', object)"
        ),
        new Put(
            uriTemplate: '/formations/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'adherent_formations') and is_granted('SCOPE_CAN_MANAGE', object)"
        ),
        new Post(
            uriTemplate: '/formations/{uuid}/file',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: FormationUploadFileController::class,
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'adherent_formations') and is_granted('SCOPE_CAN_MANAGE', object)"
        ),
        new Get(
            uriTemplate: '/formations/{uuid}/file',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: FormationDownloadFileController::class,
            security: "is_granted('ROLE_USER')"
        ),
        new Get(
            uriTemplate: '/formations/{uuid}/link',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: FormationLinkController::class,
            security: "is_granted('ROLE_USER')"
        ),
        new Delete(
            uriTemplate: '/formations/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'adherent_formations') and is_granted('SCOPE_CAN_MANAGE', object)"
        ),
        new GetCollection(
            uriTemplate: '/formations',
            paginationMaximumItemsPerPage: 1000,
            paginationClientEnabled: true,
            normalizationContext: ['groups' => ['formation_list_read']],
            security: "is_granted('ROLE_USER')"
        ),
        new Post(uriTemplate: '/formations'),
    ],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['formation_read']],
    denormalizationContext: ['groups' => ['formation_write']],
    order: ['createdAt' => 'DESC'],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'adherent_formations')"
)]
#[FormationContent]
#[ORM\Entity(repositoryClass: FormationRepository::class)]
#[ORM\EntityListeners([AdherentFormationListener::class])]
#[ORM\Table(name: 'adherent_formation')]
#[ScopeVisibility]
#[UniqueEntity(fields: ['zone', 'title'], message: 'adherent_formation.zone_title.unique_entity')]
class Formation implements EntityScopeVisibilityWithZoneInterface, EntityAdherentBlameableInterface, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;
    use EntityScopeVisibilityTrait;
    use PositionTrait;

    #[Assert\Sequentially([
        new Assert\NotBlank(message: 'Veuillez renseigner un titre.'),
        new Assert\Length(min: 2, minMessage: 'Le titre doit faire au moins 2 caractères.'),
    ])]
    #[Groups(['formation_read', 'formation_list_read', 'formation_write'])]
    #[ORM\Column]
    private ?string $title = null;

    #[Assert\Length(min: 2, minMessage: 'La description doit faire au moins 2 caractères.')]
    #[Groups(['formation_read', 'formation_list_read', 'formation_write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Groups(['formation_read', 'formation_list_read', 'formation_write'])]
    #[ORM\Column(nullable: true)]
    private ?string $category = null;

    #[Assert\Choice(choices: FormationContentTypeEnum::ALL)]
    #[Assert\NotBlank]
    #[Groups(['formation_read', 'formation_list_read', 'formation_write'])]
    #[ORM\Column]
    private string $contentType = FormationContentTypeEnum::FILE;

    #[Assert\File(maxSize: '5M', binaryFormat: false, mimeTypes: ['image/*', 'video/mpeg', 'video/mp4', 'video/quicktime', 'video/webm', 'application/pdf', 'application/x-pdf', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/rtf', 'text/plain', 'text/csv', 'text/html', 'text/calendar'])]
    private ?UploadedFile $file = null;

    #[ORM\Column(nullable: true)]
    private ?string $filePath = null;

    #[Assert\Url]
    #[Groups(['formation_read', 'formation_list_read', 'formation_write', 'formation_read_link'])]
    #[ORM\Column(nullable: true)]
    private ?string $link = null;

    #[Groups(['formation_read', 'formation_list_read', 'formation_write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $published = false;

    #[Groups(['formation_read', 'formation_list_read'])]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    private int $printCount = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $valid = false;

    /**
     * @var Collection|Adherent[]
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'adherent_formation_print_by_adherents')]
    #[ORM\ManyToMany(targetEntity: Adherent::class, fetch: 'EXTRA_LAZY')]
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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
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
