<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\AuthoredTrait;
use App\Entity\AuthorInterface;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Validator\RiposteOpenGraph;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="jecoute_riposte")
 *
 * @ApiResource(
 *     attributes={
 *         "pagination_enabled": false,
 *         "access_control": "is_granted('ROLE_NATIONAL') or (is_granted('ROLE_ADHERENT') and is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))",
 *         "normalization_context": {"groups": {"riposte_read"}},
 *         "denormalization_context": {"groups": {"riposte_write"}},
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/ripostes",
 *             "normalization_context": {"groups": {"riposte_list_read"}},
 *         },
 *         "post": {
 *             "path": "/v3/ripostes",
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/ripostes/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "normalization_context": {"groups": {"riposte_read"}},
 *         },
 *         "put": {
 *             "path": "/v3/ripostes/{id}",
 *             "requirements": {"id": "%pattern_uuid%"}
 *         },
 *         "delete": {
 *             "path": "/v3/ripostes/{id}",
 *             "requirements": {"id": "%pattern_uuid%"}
 *         },
 *         "increment": {
 *             "method": "PUT",
 *             "path": "/v3/ripostes/{uuid}/action/{action}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "controller": "App\Controller\Api\Jecoute\IncrementRiposteStatsCounterController",
 *             "defaults": {"_api_receive": false},
 *         },
 *     }
 * )
 *
 * @RiposteOpenGraph
 */
class Riposte implements AuthorInterface
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
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @Groups({"riposte_list_read", "riposte_read", "riposte_write"})
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     *
     * @Groups({"riposte_list_read", "riposte_read", "riposte_write"})
     */
    private $body;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Url
     *
     * @Groups({"riposte_list_read", "riposte_read", "riposte_write"})
     */
    private $sourceUrl;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     *
     * @Assert\Type("bool")
     *
     * @Groups({"riposte_list_read", "riposte_read", "riposte_write"})
     */
    private $withNotification;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     *
     * @Assert\Type("bool")
     *
     * @Groups({"riposte_list_read", "riposte_read", "riposte_write"})
     */
    private $enabled;

    /**
     * @var array|null
     *
     * @ORM\Column(type="json_array")
     *
     * @Groups({"riposte_list_read", "riposte_read"})
     */
    protected $openGraph;

    /**
     * @var Administrator|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $createdBy;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    private $nbViews = 0;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    private $ndDetailViews = 0;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    private $nbSourceViews = 0;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    private $nbRipostes = 0;

    public function __construct(UuidInterface $uuid = null, $withNotification = true, $enabled = true)
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

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getNbViews(): ?int
    {
        return $this->nbViews;
    }

    public function incrementNbViews(): void
    {
        ++$this->nbViews;
    }

    public function getNdDetailViews(): ?int
    {
        return $this->ndDetailViews;
    }

    public function incrementNdDetailViews(): void
    {
        ++$this->ndDetailViews;
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

    public function __toString(): string
    {
        return (string) $this->title;
    }
}
