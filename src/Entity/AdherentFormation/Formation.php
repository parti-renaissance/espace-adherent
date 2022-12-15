<?php

namespace App\Entity\AdherentFormation;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\ScopeVisibilityFilter;
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
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order": {"createdAt": "DESC"},
 *         "normalization_context": {
 *             "groups": {"formation_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"formation_write"}
 *         },
 *         "security": "is_granted('IS_FEATURE_GRANTED', 'formation')"
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/formations",
 *             "normalization_context": {
 *                 "groups": {"formation_list_read"}
 *             },
 *             "maximum_items_per_page": 1000
 *         },
 *         "post": {
 *             "path": "/v3/formations",
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/formations/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('IS_FEATURE_GRANTED', 'formation') and is_granted('SCOPE_CAN_MANAGE', object)"
 *         },
 *         "put": {
 *             "path": "/v3/formations/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('IS_FEATURE_GRANTED', 'formation') and is_granted('SCOPE_CAN_MANAGE', object)"
 *         }
 *     }
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "name": "partial",
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
     *
     * @SymfonySerializer\Groups({
     *     "formation_read",
     *     "formation_list_read",
     *     "formation_write",
     * })
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(allowEmptyString=true, min=2, minMessage="La description doit faire au moins 2 caractères.")
     *
     * @SymfonySerializer\Groups({
     *     "formation_read",
     *     "formation_list_read",
     *     "formation_write",
     * })
     */
    private ?string $description = null;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices=FormationContentTypeEnum::ALL)
     */
    private ?string $contentType = FormationContentTypeEnum::FILE;

    /**
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\AdherentFormation\File",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     *
     * @Assert\Valid
     *
     * @SymfonySerializer\Groups({
     *     "formation_read",
     *     "formation_list_read",
     *     "formation_write",
     * })
     */
    private ?File $file = null;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     *
     * @SymfonySerializer\Groups({
     *     "formation_read",
     *     "formation_list_read",
     *     "formation_write",
     * })
     */
    private ?string $link = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @SymfonySerializer\Groups({
     *     "formation_read",
     *     "formation_list_read",
     *     "formation_write",
     * })
     */
    private bool $published = false;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @SymfonySerializer\Groups({
     *     "formation_read",
     *     "formation_list_read",
     * })
     */
    protected $printCount = 0;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString()
    {
        return (string) $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): void
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

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;
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

    public function getPrintCount(): int
    {
        return $this->printCount;
    }

    public function incrementPrintCount(): void
    {
        ++$this->printCount;
    }
}
