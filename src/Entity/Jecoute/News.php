<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Api\Filter\JecouteNewsScopeFilter;
use App\Api\Filter\JecouteNewsZipCodeFilter;
use App\Api\Filter\ScopeVisibilityFilter;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\AuthoredInterface;
use App\Entity\AuthoredTrait;
use App\Entity\AuthorInterface;
use App\Entity\EntityScopeVisibilityInterface;
use App\Entity\EntityScopeVisibilityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\Geo\Zone;
use App\Entity\IndexableEntityInterface;
use App\Jecoute\JecouteSpaceEnum;
use App\Validator\Jecoute\NewsTarget;
use App\Validator\Jecoute\ReferentNews;
use App\Validator\Scope\ScopeVisibility;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {"groups": {"jecoute_news_read"}},
 *         "denormalization_context": {"groups": {"jecoute_news_write"}},
 *         "filters": {JecouteNewsZipCodeFilter::class, JecouteNewsScopeFilter::class},
 *         "order": {"createdAt": "DESC"},
 *     },
 *     collectionOperations={
 *         "get_public": {
 *             "path": "/jecoute/news",
 *             "method": "GET",
 *             "access_control": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "uuid",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "Filter News by exact uuid.",
 *                         "example": "a046adbe-9c7b-56a9-a676-6151a6785dda",
 *                     },
 *                     {
 *                         "name": "title",
 *                         "in": "query",
 *                         "type": "string",
 *                         "description": "Filter News by partial title.",
 *                         "example": "Rassem",
 *                     },
 *                 }
 *             }
 *         },
 *         "get_private": {
 *             "path": "/v3/jecoute/news",
 *             "method": "GET",
 *             "normalization_context": {"groups": {"jecoute_news_read_dc"}},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'news')",
 *         },
 *         "post": {
 *             "path": "/v3/jecoute/news",
 *             "normalization_context": {"groups": {"jecoute_news_read_dc"}},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'news')",
 *         },
 *     },
 *     itemOperations={
 *         "get_public": {
 *             "method": "GET",
 *             "path": "/jecoute/news/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')",
 *             "swagger_context": {
 *                 "summary": "Retrieves a News resource by UUID.",
 *                 "description": "Retrieves a News resource by UUID.",
 *                 "parameters": {
 *                     {
 *                         "name": "uuid",
 *                         "in": "path",
 *                         "type": "string",
 *                         "description": "The UUID of the News resource.",
 *                         "example": "28",
 *                     }
 *                 }
 *             }
 *         },
 *         "get_private": {
 *             "path": "/v3/jecoute/news/{id}",
 *             "method": "GET",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "normalization_context": {"groups": {"jecoute_news_read_dc"}},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'news')",
 *         },
 *         "put": {
 *             "path": "/v3/jecoute/news/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "normalization_context": {"groups": {"jecoute_news_read_dc"}},
 *             "access_control": "is_granted('IS_FEATURE_GRANTED', 'news') and is_granted('CAN_CHANGE_JECOUTE_NEWS', object)",
 *         },
 *     },
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={
 *     "uuid": "exact",
 *     "title": "partial",
 * })
 * @ApiFilter(ScopeVisibilityFilter::class)
 *
 * @ORM\Table(name="jecoute_news")
 * @ORM\Entity
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="author",
 *         joinColumns={
 *             @ORM\JoinColumn(onDelete="SET NULL")
 *         }
 *     )
 * })
 *
 * @ORM\EntityListeners({"App\EntityListener\AlgoliaIndexListener"})
 *
 * @ReferentNews
 * @NewsTarget(groups="Admin")
 * @ScopeVisibility
 */
class News implements AuthoredInterface, AuthorInterface, IndexableEntityInterface, EntityScopeVisibilityInterface
{
    use EntityTimestampableTrait;
    use AuthoredTrait;
    use EntityScopeVisibilityTrait;

    /**
     * @ApiProperty(identifier=false)
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private ?int $id = null;

    /**
     * @ApiProperty(identifier=true)
     *
     * @ORM\Column(type="uuid", unique=true)
     *
     * @SymfonySerializer\Groups({"jecoute_news_read", "jecoute_news_read_dc"})
     */
    private UuidInterface $uuid;

    /**
     * @ORM\Column
     *
     * @Assert\Length(max=120)
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"jecoute_news_read", "jecoute_news_write", "jecoute_news_read_dc"})
     */
    private ?string $title;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\Length(max=1000)
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"jecoute_news_read", "jecoute_news_write", "jecoute_news_read_dc"})
     */
    private ?string $text;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     *
     * @SymfonySerializer\Groups({"jecoute_news_read", "jecoute_news_read_dc", "jecoute_news_write"})
     */
    private ?string $externalLink;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $topic;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private ?Administrator $createdBy = null;

    /**
     * @SymfonySerializer\Groups({"jecoute_news_write_national"})
     */
    private bool $global = false;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     *
     * @SymfonySerializer\Groups({"jecoute_news_read_dc", "jecoute_news_write"})
     */
    private bool $notification;

    /**
     * @ORM\Column(type="boolean", options={"default": 1})
     *
     * @SymfonySerializer\Groups({"jecoute_news_read_dc", "jecoute_news_write"})
     */
    private bool $published;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $space = null;

    public function __construct(
        UuidInterface $uuid = null,
        string $title = null,
        string $text = null,
        string $topic = null,
        string $externalLink = null,
        Zone $zone = null,
        bool $notification = false,
        bool $published = true
    ) {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->title = $title;
        $this->text = $text;
        $this->topic = $topic;
        $this->externalLink = $externalLink;
        $this->notification = $notification;
        $this->published = $published;

        $this->setZone($zone);
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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    public function setExternalLink(?string $externalLink): void
    {
        $this->externalLink = $externalLink;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(?string $topic): void
    {
        $this->topic = $topic;
    }

    public function getCreatedBy(): ?Administrator
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Administrator $createdBy): void
    {
        $this->createdBy = $createdBy;
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

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getSpace(): ?string
    {
        return $this->space;
    }

    public function setSpace(?string $space): void
    {
        if (null !== $space && !JecouteSpaceEnum::isValid($space)) {
            throw new \InvalidArgumentException('Invalid space');
        }
        $this->space = $space;
    }

    public function getIndexOptions(): array
    {
        return [];
    }

    public function isIndexable(): bool
    {
        return $this->isPublished();
    }
}
