<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
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
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
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
use App\JeMengage\Hit\HitTargetInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\Scope\ScopeVisibilityEnum;
use App\Utils\StringCleaner;
use App\Validator\Jecoute\NewsContent;
use App\Validator\Jecoute\NewsTarget;
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
            security: "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')"
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
            security: "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')"
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
#[NewsContent]
#[NewsTarget(groups: ['Admin'])]
#[ORM\AssociationOverrides([new ORM\AssociationOverride(name: 'author', joinColumns: [new ORM\JoinColumn(onDelete: 'SET NULL')])])]
#[ORM\Entity]
#[ORM\EntityListeners([AlgoliaIndexListener::class])]
#[ORM\Table(name: 'jecoute_news')]
#[ScopeVisibility]
class News implements AuthorInstanceInterface, UserDocumentInterface, IndexableEntityInterface, EntityScopeVisibilityWithZoneInterface, NotificationObjectInterface, EntityAdministratorBlameableInterface, HitTargetInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use AuthorInstanceTrait;
    use EntityScopeVisibilityTrait {
        setZone as traitSetZone;
    }
    use UserDocumentTrait;
    use EntityAdministratorBlameableTrait;

    #[Assert\Length(max: 120)]
    #[Assert\NotBlank]
    #[Groups(['jecoute_news_read', 'jecoute_news_write', 'jecoute_news_read_dc'])]
    #[ORM\Column]
    private ?string $title;

    #[Groups(['jecoute_news_read', 'jecoute_news_write', 'jecoute_news_read_dc'])]
    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    #[Assert\Url]
    #[Groups(['jecoute_news_read', 'jecoute_news_read_dc', 'jecoute_news_write'])]
    #[ORM\Column(nullable: true)]
    private ?string $externalLink;

    #[Assert\Expression('value !== null or null === this.getExternalLink()', message: 'news.link_label.required')]
    #[Assert\Length(max: 30)]
    #[Groups(['jecoute_news_read', 'jecoute_news_read_dc', 'jecoute_news_write'])]
    #[ORM\Column(nullable: true)]
    private ?string $linkLabel;

    #[ORM\Column(nullable: true)]
    private ?string $topic;

    #[Groups(['jecoute_news_write_national'])]
    private bool $global = true;

    #[Groups(['jecoute_news_read_dc', 'jecoute_news_write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private bool $notification;

    #[Groups(['jecoute_news_read_dc', 'jecoute_news_write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private bool $published;

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
        ?string $content = null,
        ?string $externalLink = null,
        ?string $linkLabel = null,
        ?Zone $zone = null,
        bool $notification = true,
        bool $published = true,
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->title = $title;
        $this->content = $content;
        $this->externalLink = $externalLink;
        $this->linkLabel = $linkLabel;
        $this->notification = $notification;
        $this->published = $published;

        $this->setZone($zone);
        $this->documents = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->title;
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
        $content = (new UnicodeString(StringCleaner::removeMarkdown($this->content)));

        if ($length) {
            $content = $content->truncate($length, '…', false);
        }

        return $content->toString();
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
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

    public function setZone(?Zone $zone): void
    {
        $this->traitSetZone($zone);
        $this->updateVisibility();
    }

    public function isIndexable(): bool
    {
        return $this->isPublished();
    }

    public function getContentContainingDocuments(): string
    {
        return (string) $this->content;
    }

    public function getFieldContainingDocuments(): string
    {
        return 'content';
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

    public function isNational(): bool
    {
        return $this->isNationalVisibility();
    }
}
