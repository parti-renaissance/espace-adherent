<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Api\Filter\JecouteNewsScopeFilter;
use App\Api\Filter\JecouteNewsZipCodeFilter;
use App\Api\Filter\ScopeVisibilityFilter;
use App\Entity\AuthorInstanceInterface;
use App\Entity\AuthorInstanceTrait;
use App\Entity\Committee;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityScopeVisibilityTrait;
use App\Entity\EntityScopeVisibilityWithZoneInterface;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\IndexableEntityInterface;
use App\Entity\NotificationObjectInterface;
use App\Entity\UserDocument;
use App\Entity\UserDocumentInterface;
use App\Entity\UserDocumentTrait;
use App\EntityListener\AlgoliaIndexListener;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\Scope\ScopeVisibilityEnum;
use App\Utils\StringCleaner;
use App\Validator\Jecoute\NewsTarget;
use App\Validator\Jecoute\NewsText;
use App\Validator\Scope\ScopeVisibility;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: SearchFilter::class, properties: ['uuid' => 'exact', 'title' => 'partial'])]
#[ApiFilter(filterClass: ScopeVisibilityFilter::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/jecoute/news/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: 'is_granted(\'ROLE_OAUTH_SCOPE_JEMARCHE_APP\')'
        ),
        new Get(
            uriTemplate: '/v3/jecoute/news/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['jecoute_news_read_dc']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'news')"
        ),
        new Put(
            uriTemplate: '/v3/jecoute/news/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['jecoute_news_read_dc']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'news') and is_granted('SCOPE_CAN_MANAGE', object)"
        ),
        new GetCollection(
            uriTemplate: '/jecoute/news',
            security: 'is_granted(\'ROLE_OAUTH_SCOPE_JEMARCHE_APP\')'
        ),
        new GetCollection(
            uriTemplate: '/v3/jecoute/news',
            normalizationContext: ['groups' => ['jecoute_news_read_dc']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'news')"
        ),
        new Post(
            uriTemplate: '/v3/jecoute/news',
            normalizationContext: ['groups' => ['jecoute_news_read_dc']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'news')"
        ),
    ],
    normalizationContext: ['groups' => ['jecoute_news_read']],
    denormalizationContext: ['groups' => ['jecoute_news_write']],
    filters: [JecouteNewsZipCodeFilter::class, JecouteNewsScopeFilter::class],
    order: ['createdAt' => 'DESC']
)]
#[NewsTarget(groups: ['Admin'])]
#[NewsText]
#[ORM\AssociationOverrides([new ORM\AssociationOverride(name: 'author', joinColumns: [new ORM\JoinColumn(onDelete: 'SET NULL')])])]
#[ORM\Entity]
#[ORM\EntityListeners([AlgoliaIndexListener::class])]
#[ORM\Table(name: 'jecoute_news')]
#[ScopeVisibility]
class News implements AuthorInstanceInterface, UserDocumentInterface, IndexableEntityInterface, EntityScopeVisibilityWithZoneInterface, NotificationObjectInterface
{
    use EntityTimestampableTrait;
    use AuthorInstanceTrait;
    use EntityScopeVisibilityTrait {
        setZone as traitSetZone;
    }
    use UserDocumentTrait;
    use EntityAdministratorBlameableTrait;

    #[ApiProperty(identifier: false)]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ApiProperty(identifier: true)]
    #[Groups(['jecoute_news_read', 'jecoute_news_read_dc'])]
    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidInterface $uuid;

    #[Assert\Length(max: 120)]
    #[Assert\NotBlank]
    #[Groups(['jecoute_news_read', 'jecoute_news_write', 'jecoute_news_read_dc'])]
    #[ORM\Column]
    private ?string $title;

    #[Groups(['jecoute_news_read', 'jecoute_news_write', 'jecoute_news_read_dc'])]
    #[ORM\Column(type: 'text')]
    private ?string $text;

    /**
     * Used in admin for enriched text.
     */
    private ?string $enrichedText = null;

    #[Assert\Url]
    #[Groups(['jecoute_news_read', 'jecoute_news_read_dc', 'jecoute_news_write'])]
    #[ORM\Column(nullable: true)]
    private ?string $externalLink;

    #[Assert\Expression('value !== null or (this.isEnriched() === false or null === this.getExternalLink())', message: 'news.link_label.required')]
    #[Assert\Length(max: 30)]
    #[Groups(['jecoute_news_read', 'jecoute_news_read_dc', 'jecoute_news_write'])]
    #[ORM\Column(nullable: true)]
    private ?string $linkLabel;

    #[ORM\Column(nullable: true)]
    private ?string $topic;

    #[Groups(['jecoute_news_write_national'])]
    private bool $global = false;

    #[Groups(['jecoute_news_read_dc', 'jecoute_news_write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $notification;

    #[Groups(['jecoute_news_read_dc', 'jecoute_news_write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private bool $published;

    #[Groups(['jecoute_news_read', 'jecoute_news_read_dc', 'jecoute_news_write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $pinned;

    #[Groups(['jecoute_news_read', 'jecoute_news_read_dc', 'jecoute_news_write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $enriched;

    /**
     * @var UserDocument[]|Collection
     */
    #[ORM\InverseJoinColumn(name: 'user_document_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinColumn(name: 'jecoute_news_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'jecoute_news_user_documents')]
    #[ORM\ManyToMany(targetEntity: UserDocument::class, cascade: ['all'], orphanRemoval: true)]
    protected Collection $documents;

    #[Groups(['jecoute_news_write'])]
    #[ORM\ManyToOne(targetEntity: Committee::class)]
    public ?Committee $committee = null;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?string $title = null,
        ?string $text = null,
        ?string $externalLink = null,
        ?string $linkLabel = null,
        ?Zone $zone = null,
        bool $notification = false,
        bool $published = true,
        bool $pinned = false,
        bool $enriched = false,
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->title = $title;
        $this->text = $text;
        $this->externalLink = $externalLink;
        $this->linkLabel = $linkLabel;
        $this->notification = $notification;
        $this->published = $published;
        $this->pinned = $pinned;
        $this->enriched = $enriched;

        $this->setZone($zone);
        $this->documents = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getCleanedCroppedText(?int $length = 512): ?string
    {
        if ($this->isEnriched()) {
            $content = (new UnicodeString(StringCleaner::removeMarkdown($this->text)));

            if ($length) {
                $content = $content->truncate($length, '…', false);
            }

            return $content->toString();
        }

        return $this->text;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function getEnrichedText(): ?string
    {
        return $this->enrichedText;
    }

    public function setEnrichedText(?string $text): void
    {
        $this->enrichedText = $text;
    }

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(?string $externalLink): void
    {
        $this->externalLink = $externalLink;
    }

    public function getLinkLabel(): ?string
    {
        return $this->linkLabel;
    }

    public function setLinkLabel(?string $linkLabel): void
    {
        $this->linkLabel = $linkLabel;
    }

    public function isGlobal(): bool
    {
        return $this->global;
    }

    public function setGlobal(bool $global): void
    {
        $this->global = $global;
    }

    public function isNotification(): bool
    {
        return $this->notification;
    }

    public function setNotification(bool $notification): void
    {
        $this->notification = $notification;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function isPinned(): bool
    {
        return $this->pinned;
    }

    public function setPinned(bool $pinned): void
    {
        $this->pinned = $pinned;
    }

    public function isEnriched(): bool
    {
        return $this->enriched;
    }

    public function setEnriched(bool $enriched): void
    {
        $this->enriched = $enriched;
    }

    public function setZone(?Zone $zone): void
    {
        $this->traitSetZone($zone);
        $this->updateVisibility();
    }

    public function getIndexOptions(): array
    {
        return [];
    }

    public function isIndexable(): bool
    {
        return $this->isPublished();
    }

    public function getContentContainingDocuments(): string
    {
        return (string) $this->text;
    }

    public function getFieldContainingDocuments(): string
    {
        return 'text';
    }

    public function updateVisibility(): void
    {
        if ($this->isNationalVisibility() && $this->committee) {
            $this->visibility = ScopeVisibilityEnum::LOCAL;
        }
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function isNotificationEnabled(SendNotificationCommandInterface $command): bool
    {
        return $this->isNotification();
    }

    public function handleNotificationSent(SendNotificationCommandInterface $command): void
    {
    }
}
