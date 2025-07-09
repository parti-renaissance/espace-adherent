<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Api\Jecoute\IncrementRiposteStatsCounterController;
use App\Entity\Administrator;
use App\Entity\AuthoredTrait;
use App\Entity\AuthorInterface;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\IndexableEntityInterface;
use App\EntityListener\AlgoliaIndexListener;
use App\Validator\RiposteOpenGraph;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/ripostes/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['riposte_read']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'ripostes') or (is_granted('ROLE_USER') and is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))"
        ),
        new Put(
            uriTemplate: '/v3/ripostes/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%']
        ),
        new Delete(
            uriTemplate: '/v3/ripostes/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%']
        ),
        new HttpOperation(
            method: 'PUT',
            uriTemplate: '/v3/ripostes/{uuid}/action/{action}',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: IncrementRiposteStatsCounterController::class,
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'ripostes') or (is_granted('ROLE_USER') and is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))",
            deserialize: false,
        ),
        new GetCollection(
            uriTemplate: '/v3/ripostes',
            normalizationContext: ['groups' => ['riposte_list_read']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'ripostes') or (is_granted('ROLE_USER') and is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))"
        ),
        new Post(uriTemplate: '/v3/ripostes'),
    ],
    normalizationContext: ['groups' => ['riposte_read']],
    denormalizationContext: ['groups' => ['riposte_write']],
    order: ['createdAt' => 'DESC'],
    paginationEnabled: false,
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'ripostes')"
)]
#[ORM\Entity]
#[ORM\EntityListeners([AlgoliaIndexListener::class])]
#[ORM\Table(name: 'jecoute_riposte')]
#[RiposteOpenGraph]
class Riposte implements AuthorInterface, IndexableEntityInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use AuthoredTrait;

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
    #[Groups(['riposte_list_read', 'riposte_read', 'riposte_write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $withNotification;

    /**
     * @var bool
     */
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

    public function isIndexable(): bool
    {
        return $this->isEnabled();
    }
}
