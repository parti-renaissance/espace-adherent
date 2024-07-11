<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\AdherentMessage\StaticSegmentInterface;
use App\AdherentSegment\AdherentSegmentTypeEnum;
use App\EntityListener\AdherentSegmentListener;
use App\Repository\AdherentSegmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "post": {
 *             "path": "/adherent-segments",
 *             "security": "is_granted('ROLE_MESSAGE_REDACTOR')",
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
#[ORM\Entity(repositoryClass: AdherentSegmentRepository::class)]
#[ORM\EntityListeners([AdherentSegmentListener::class])]
class AdherentSegment implements AuthorInterface, StaticSegmentInterface
{
    use EntityIdentityTrait;
    use StaticSegmentTrait;
    use AuthoredTrait;

    /**
     * @var string
     */
    #[Groups(['public', 'write'])]
    #[ORM\Column]
    #[Assert\NotBlank]
    private $label;

    /**
     * @var array
     */
    #[Groups(['write'])]
    #[ORM\Column(type: 'simple_array')]
    #[Assert\NotBlank]
    #[Assert\Count(min: 1)]
    private $memberIds = [];

    /**
     * @var string
     */
    #[Groups(['write'])]
    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Choice(callback: [AdherentSegmentTypeEnum::class, 'toArray'])]
    private $segmentType;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    #[Assert\NotBlank]
    protected $author;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $synchronized = false;

    public function __construct(?UuidInterface $uuid = null)
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
