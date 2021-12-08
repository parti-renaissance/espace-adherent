<?php

namespace App\Entity\Event;

use App\Address\GeoCoder;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\ExposedObjectInterface;
use App\Entity\IndexableEntityInterface;
use App\Entity\PostAddress;
use App\Entity\SynchronizedEntity;
use App\Entity\UserDocument;
use App\Entity\UserDocumentInterface;
use App\Entity\UserDocumentTrait;
use App\Event\EventTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 *
 * @ORM\EntityListeners({"App\EntityListener\AlgoliaIndexListener"})
 */
class CommitteeEvent extends BaseEvent implements UserDocumentInterface, SynchronizedEntity, IndexableEntityInterface, ExposedObjectInterface
{
    use UserDocumentTrait;
    use DefaultCategoryOwnerTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee")
     *
     * @Groups({"event_read"})
     */
    private $committee;

    /**
     * @JMS\Exclude
     */
    private $type;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isForLegislatives;

    /**
     * @var UserDocument[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\UserDocument", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="event_user_documents",
     *     joinColumns={
     *         @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="user_document_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     */
    protected $documents;

    public function __construct(
        UuidInterface $uuid,
        ?Adherent $organizer,
        ?Committee $committee,
        string $name,
        EventCategory $category,
        string $description,
        PostAddress $address,
        string $beginAt,
        string $finishAt,
        int $capacity = null,
        bool $isForLegislatives = false,
        string $createdAt = null,
        int $participantsCount = 0,
        string $slug = null,
        string $type = null,
        array $referentTags = [],
        string $timeZone = GeoCoder::DEFAULT_TIME_ZONE
    ) {
        parent::__construct($uuid);

        $this->organizer = $organizer;
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
        $this->referentTags = new ArrayCollection($referentTags);
        $this->zones = new ArrayCollection();
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

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("committeeUuid")
     * @JMS\Groups({"event_read"})
     */
    public function getCommitteeUuidAsString(): ?string
    {
        if (!$committee = $this->getCommittee()) {
            return null;
        }

        return $committee->getUuidAsString();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("categoryName")
     * @JMS\Groups({"event_read"})
     */
    public function getEventCategoryName(): ?string
    {
        if (!$category = $this->getCategory()) {
            return null;
        }

        return $category->getName();
    }

    public function isReferentEvent(): bool
    {
        return null === $this->getCommittee();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("organizerUuid")
     * @JMS\Groups({"event_read"})
     */
    public function getOrganizerUuid(): ?string
    {
        if (!$organizer = $this->getOrganizer()) {
            return null;
        }

        return $organizer->getUuidAsString();
    }

    public function isIndexable(): bool
    {
        return $this->isActive() && $this->isPublished() && $this->isGeocoded();
    }

    public function getExposedRouteName(): string
    {
        return 'app_committee_event_show';
    }

    public function needNotifyForRegistration(): bool
    {
        return true;
    }

    public function needNotifyForCancellation(): bool
    {
        return true;
    }
}
