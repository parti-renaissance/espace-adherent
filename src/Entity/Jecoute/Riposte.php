<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Administrator;
use App\Entity\AuthoredTrait;
use App\Entity\AuthorInterface;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\IndexableEntityInterface;
use App\EntityListener\AlgoliaIndexListener;
use App\EntityListener\DynamicLinkListener;
use App\Firebase\DynamicLinks\DynamicLinkObjectInterface;
use App\Firebase\DynamicLinks\DynamicLinkObjectTrait;
use App\Validator\RiposteOpenGraph;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "order": {"createdAt": "DESC"},
 *         "pagination_enabled": false,
 *         "security": "is_granted('IS_FEATURE_GRANTED', 'ripostes')",
 *         "normalization_context": {"groups": {"riposte_read"}},
 *         "denormalization_context": {"groups": {"riposte_write"}},
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/ripostes",
 *             "normalization_context": {"groups": {"riposte_list_read"}},
 *             "security": "is_granted('IS_FEATURE_GRANTED', 'ripostes') or (is_granted('ROLE_USER') and is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))",
 *         },
 *         "post": {
 *             "path": "/v3/ripostes",
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/ripostes/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "normalization_context": {"groups": {"riposte_read"}},
 *             "security": "is_granted('IS_FEATURE_GRANTED', 'ripostes') or (is_granted('ROLE_USER') and is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))",
 *         },
 *         "put": {
 *             "path": "/v3/ripostes/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"}
 *         },
 *         "delete": {
 *             "path": "/v3/ripostes/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"}
 *         },
 *         "increment": {
 *             "method": "PUT",
 *             "path": "/v3/ripostes/{uuid}/action/{action}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\Jecoute\IncrementRiposteStatsCounterController",
 *             "defaults": {"_api_receive": false},
 *             "security": "is_granted('IS_FEATURE_GRANTED', 'ripostes') or (is_granted('ROLE_USER') and is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))",
 *         },
 *     }
 * )
 *
 * @RiposteOpenGraph
 */
#[ORM\Entity]
#[ORM\EntityListeners([DynamicLinkListener::class, AlgoliaIndexListener::class])]
#[ORM\Table(name: 'jecoute_riposte')]
class Riposte implements AuthorInterface, IndexableEntityInterface, DynamicLinkObjectInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use AuthoredTrait;
    use DynamicLinkObjectTrait;

    public const ACTION_VIEW = 'view';
    public const ACTION_DETAIL_VIEW = 'detail_view';
    public const ACTION_SOURCE_VIEW = 'source_view';
    public const ACTION_RIPOSTE = 'riposte';

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['riposte_list_read', 'riposte_read', 'riposte_write'])]
    #[ORM\Column]
    private $title;

    /**
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Groups(['riposte_list_read', 'riposte_read', 'riposte_write'])]
    #[ORM\Column(type: 'text')]
    private $body;

    /**
     * @var string|null
     */
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Groups(['riposte_list_read', 'riposte_read', 'riposte_write'])]
    #[ORM\Column]
    private $sourceUrl;

    /**
     * @var bool
     */
    #[Assert\Type('bool')]
    #[Groups(['riposte_list_read', 'riposte_read', 'riposte_write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $withNotification;

    /**
     * @var bool
     */
    #[Assert\Type('bool')]
    #[Groups(['riposte_list_read', 'riposte_read', 'riposte_write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $enabled;

    /**
     * @var array|null
     */
    #[Groups(['riposte_list_read', 'riposte_read'])]
    #[ORM\Column(type: 'json')]
    protected $openGraph;

    /**
     * @var Administrator|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private $createdBy;

    /**
     * @var int|null
     */
    #[Groups(['riposte_read_dc'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private $nbViews = 0;

    /**
     * @var int|null
     */
    #[Groups(['riposte_read_dc'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private $nbDetailViews = 0;

    /**
     * @var int|null
     */
    #[Groups(['riposte_read_dc'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private $nbSourceViews = 0;

    /**
     * @var int|null
     */
    #[Groups(['riposte_read_dc'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private $nbRipostes = 0;

    public function __construct(?UuidInterface $uuid = null, $withNotification = true, $enabled = true)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->withNotification = $withNotification;
        $this->enabled = $enabled;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    public function getSourceUrl(): ?string
    {
        return $this->sourceUrl;
    }

    public function setSourceUrl(?string $sourceUrl): void
    {
        $this->sourceUrl = $sourceUrl;
    }

    public function isWithNotification(): bool
    {
        return $this->withNotification;
    }

    public function setWithNotification(bool $withNotification): void
    {
        $this->withNotification = $withNotification;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getOpenGraph(): ?array
    {
        return $this->openGraph;
    }

    public function setOpenGraph(array $openGraph): void
    {
        $this->openGraph = $openGraph;
    }

    public function clearOpenGraph(): void
    {
        $this->openGraph = null;
    }

    public function getCreatedBy(): ?Administrator
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Administrator $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getNbViews(): ?int
    {
        return $this->nbViews;
    }

    public function incrementNbViews(): void
    {
        ++$this->nbViews;
    }

    public function getNbDetailViews(): ?int
    {
        return $this->nbDetailViews;
    }

    public function incrementNbDetailViews(): void
    {
        ++$this->nbDetailViews;
    }

    public function getNbSourceViews(): ?int
    {
        return $this->nbSourceViews;
    }

    public function incrementNbSourceViews(): void
    {
        ++$this->nbSourceViews;
    }

    public function getNbRipostes(): ?int
    {
        return $this->nbRipostes;
    }

    public function incrementNbRipostes(): void
    {
        ++$this->nbRipostes;
    }

    #[Groups(['riposte_read_dc'])]
    public function getCreator(): string
    {
        return $this->author ? $this->author->getFullName() : 'Admin';
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getIndexOptions(): array
    {
        return [];
    }

    public function isIndexable(): bool
    {
        return $this->isEnabled();
    }

    public function getDynamicLinkPath(): string
    {
        return '/ripostes/'.$this->uuid;
    }

    public function withSocialMeta(): bool
    {
        return true;
    }

    public function getSocialTitle(): string
    {
        return (string) $this->getTitle();
    }
}
