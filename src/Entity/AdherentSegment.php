<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\AdherentMessage\StaticSegmentInterface;
use App\AdherentSegment\AdherentSegmentTypeEnum;
use App\EntityListener\AdherentSegmentListener;
use App\Repository\AdherentSegmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/adherent-segments',
            normalizationContext: ['iri' => true, 'groups' => ['public']],
            denormalizationContext: ['groups' => ['write']],
            security: "is_granted('ROLE_MESSAGE_REDACTOR')"
        ),
    ]
)]
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
    #[Assert\NotBlank]
    #[Groups(['public', 'write'])]
    #[ORM\Column]
    private $label;

    /**
     * @var array
     */
    #[Assert\Count(min: 1)]
    #[Assert\NotBlank]
    #[Groups(['write'])]
    #[ORM\Column(type: 'simple_array')]
    private $memberIds = [];

    /**
     * @var string
     */
    #[Assert\Choice(callback: [AdherentSegmentTypeEnum::class, 'toArray'])]
    #[Assert\NotBlank]
    #[Groups(['write'])]
    #[ORM\Column(nullable: true)]
    private $segmentType;

    /**
     * @var Adherent|null
     */
    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
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
