<?php

namespace App\Entity\Event;

use App\Address\AddressInterface;
use App\Address\GeoCoder;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\UserDocument;
use App\Entity\UserDocumentInterface;
use App\Entity\UserDocumentTrait;
use App\Event\EventTypeEnum;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class CommitteeEvent extends BaseEvent implements UserDocumentInterface
{
    use UserDocumentTrait;

    #[Groups(['event_read', 'event_write_creation'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Committee::class)]
    private $committee;

    private $type;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $isForLegislatives;

    /**
     * @var UserDocument[]|Collection
     */
    #[ORM\InverseJoinColumn(name: 'user_document_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'event_user_documents')]
    #[ORM\ManyToMany(targetEntity: UserDocument::class, cascade: ['persist'])]
    protected Collection $documents;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?Adherent $organizer = null,
        ?Committee $committee = null,
        ?string $name = null,
        ?EventCategory $category = null,
        ?string $description = null,
        ?AddressInterface $address = null,
        ?string $beginAt = null,
        ?string $finishAt = null,
        ?int $capacity = null,
        bool $isForLegislatives = false,
        ?string $createdAt = null,
        int $participantsCount = 0,
        ?string $slug = null,
        ?string $type = null,
        string $timeZone = GeoCoder::DEFAULT_TIME_ZONE,
    ) {
        parent::__construct($uuid);

        $this->author = $organizer;
        $this->committee = $committee;
        $this->setName($name);
        $this->slug = $slug;
        $this->category = $category;
        $this->description = $description;
        $this->postAddress = $address;
        $this->capacity = $capacity;
        $this->participantsCount = $participantsCount;
        $this->beginAt = new \DateTime($beginAt);
        $this->finishAt = new \DateTime($finishAt);
        $this->createdAt = new \DateTime($createdAt ?: 'now');
        $this->updatedAt = new \DateTime($createdAt ?: 'now');
        $this->isForLegislatives = $isForLegislatives;
        $this->type = $type;
        $this->documents = new ArrayCollection();
        $this->timeZone = $timeZone;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function getType(): string
    {
        return $this->type ?? EventTypeEnum::TYPE_COMMITTEE;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function isForLegislatives()
    {
        return $this->isForLegislatives;
    }

    public function setIsForLegislatives(bool $isForLegislatives): void
    {
        $this->isForLegislatives = $isForLegislatives;
    }

    public function getCommitteeUuid(): ?string
    {
        if (!$committee = $this->getCommittee()) {
            return null;
        }

        return $committee->getUuidAsString();
    }

    public function needNotifyForRegistration(): bool
    {
        return true;
    }

    public function getContentContainingDocuments(): string
    {
        return (string) $this->description;
    }

    public function getFieldContainingDocuments(): string
    {
        return 'description';
    }
}
