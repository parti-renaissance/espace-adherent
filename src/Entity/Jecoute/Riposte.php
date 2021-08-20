<?php

namespace App\Entity\Jecoute;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\Filter\ActiveRipostesFilter;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\AuthoredTrait;
use App\Entity\AuthorInterface;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
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
 *         "access_control": "is_granted('ROLE_NATIONAL')",
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
 *     }
 * )
 *
 * @ApiFilter(ActiveRipostesFilter::class)
 */
class Riposte implements AuthorInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use AuthoredTrait;

    /**
     * @var string
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
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     *
     * @Groups({"riposte_list_read", "riposte_read", "riposte_write"})
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     *
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
     * @Groups({"riposte_read", "riposte_write"})
     */
    private $enabled;

    /**
     * @var Administrator|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $createdBy;

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

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): void
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

    public function __toString(): string
    {
        return (string) $this->title;
    }
}
