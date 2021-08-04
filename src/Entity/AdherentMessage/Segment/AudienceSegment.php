<?php

namespace App\Entity\AdherentMessage\Segment;

use ApiPlatform\Core\Annotation\ApiResource;
use App\AdherentMessage\StaticSegmentInterface;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AuthorInterface;
use App\Entity\EntityIdentityTrait;
use App\Entity\StaticSegmentTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {"groups": {"audience_segment_read"}},
 *         "denormalization_context": {"groups": {"audience_segment_write"}},
 *     },
 *     collectionOperations={
 *         "post": {
 *             "path": "/v3/audience-segments",
 *             "access_control": "is_granted('ROLE_MESSAGE_REDACTOR') and is_granted('ROLE_AUDIENCE')",
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/audience-segments/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_MESSAGE_REDACTOR') and is_granted('ROLE_AUDIENCE') and object.getAuthor() == user",
 *         },
 *         "put": {
 *             "path": "/v3/audience-segments/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_MESSAGE_REDACTOR') and is_granted('ROLE_AUDIENCE') and object.getAuthor() == user",
 *         },
 *         "delete": {
 *             "path": "/v3/audience-segments/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('ROLE_MESSAGE_REDACTOR') and is_granted('ROLE_AUDIENCE') and object.getAuthor() == user",
 *         },
 *     }
 * )
 */
class AudienceSegment implements AuthorInterface, StaticSegmentInterface
{
    use EntityIdentityTrait;
    use StaticSegmentTrait;

    /**
     * @var AudienceFilter|null
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\AdherentMessage\Filter\AudienceFilter",
     *     cascade={"all"},
     *     fetch="EAGER",
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\Valid
     * @Assert\NotNull
     *
     * @Groups({"audience_segment_read", "audience_segment_write"})
     */
    private $filter;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     */
    private $author;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", options={"unsigned": true}, nullable=true)
     *
     * @Groups({"audience_segment_read"})
     */
    private $recipientCount;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Groups({"audience_segment_read"})
     */
    private $synchronized = false;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getFilter(): ?AudienceFilter
    {
        return $this->filter;
    }

    public function setFilter(?AudienceFilter $filter): void
    {
        $this->filter = $filter;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(?Adherent $author): void
    {
        $this->author = $author;
    }

    public function getRecipientCount(): ?int
    {
        return $this->recipientCount;
    }

    public function setRecipientCount(?int $recipientCount): void
    {
        $this->recipientCount = $recipientCount;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function setSynchronized(bool $synchronized): void
    {
        $this->synchronized = $synchronized;
    }
}
