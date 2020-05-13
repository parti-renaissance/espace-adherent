<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\AdherentMessage\StaticSegmentInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentSegmentRepository")
 * @ORM\EntityListeners({"App\EntityListener\AdherentSegmentListener"})
 *
 * @ApiResource(
 *     collectionOperations={
 *         "post": {
 *             "path": "/adherent-segments",
 *             "access_control": "is_granted('ROLE_MESSAGE_REDACTOR')",
 *             "normalization_context": {
 *                 "iri": true,
 *                 "groups": {"public"}
 *             },
 *             "denormalization_context": {"groups": {"write"}}
 *         }
 *     },
 *     itemOperations={}
 * )
 */
class AdherentSegment implements AuthorInterface, StaticSegmentInterface
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     *
     * @Groups({"public", "write"})
     */
    private $label;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     *
     * @Assert\NotBlank
     * @Assert\Count(min=1)
     *
     * @Groups({"write"})
     */
    private $memberIds = [];

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\AdherentSegment\AdherentSegmentTypeEnum", "toArray"}, strict=true)
     *
     * @Groups({"write"})
     */
    private $segmentType;

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
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $mailchimpId;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $synchronized = false;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getMemberIds(): array
    {
        return $this->memberIds;
    }

    public function setMemberIds(array $memberIds): void
    {
        $this->memberIds = $memberIds;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(?Adherent $author): void
    {
        $this->author = $author;
    }

    public function getMailchimpId(): ?int
    {
        return $this->mailchimpId;
    }

    public function setMailchimpId(int $mailchimpId): void
    {
        $this->mailchimpId = $mailchimpId;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function setSynchronized(bool $synchronized): void
    {
        $this->synchronized = $synchronized;
    }

    public function getSegmentType(): ?string
    {
        return $this->segmentType;
    }

    public function setSegmentType(string $segmentType): void
    {
        $this->segmentType = $segmentType;
    }
}
