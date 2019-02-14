<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Address\GeoCoder;
use AppBundle\Report\ReportType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 *
 * @Algolia\Index
 */
class Event extends BaseEvent implements UserDocumentInterface, SynchronizedEntity, ReferentTaggableEntity
{
    use UserDocumentTrait;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EventCategory")
     *
     * @Algolia\Attribute
     */
    protected $category;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee")
     *
     * @Algolia\Attribute
     */
    private $committee;

    /**
     * @JMS\Exclude
     */
    private $type;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isForLegislatives = false;

    /**
     * @var UserDocument[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\UserDocument", cascade={"persist"})
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

    /**
     * @var Collection|ReferentTag[]
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ReferentTag")
     */
    private $referentTags;

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
        $this->uuid = $uuid;
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
        $this->status = self::STATUS_SCHEDULED;
        $this->isForLegislatives = $isForLegislatives;
        $this->type = $type;
        $this->documents = new ArrayCollection();
        $this->referentTags = new ArrayCollection($referentTags);
        $this->timeZone = $timeZone;
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    public function update(
        string $name,
        EventCategory $category,
        string $description,
        PostAddress $address,
        string $timeZone,
        string $beginAt,
        string $finishAt,
        int $capacity = null,
        bool $isForLegislatives = false
    ) {
        $this->setName($name);
        $this->category = $category;
        $this->capacity = $capacity;
        $this->timeZone = $timeZone;
        $this->beginAt = new \DateTime($beginAt);
        $this->finishAt = new \DateTime($finishAt);
        $this->description = $description;
        $this->isForLegislatives = $isForLegislatives;

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }
    }

    public function getCategory(): EventCategory
    {
        return $this->category;
    }

    public function getType(): ?string
    {
        return $this->type ?? $this::EVENT_TYPE;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function isForLegislatives()
    {
        return $this->isForLegislatives;
    }

    public function getReportType(): string
    {
        return ReportType::COMMUNITY_EVENT;
    }

    /**
     * @return Collection|ReferentTag[]
     */
    public function getReferentTags(): Collection
    {
        return $this->referentTags;
    }

    public function addReferentTag(ReferentTag $referentTag): void
    {
        if (!$this->referentTags->contains($referentTag)) {
            $this->referentTags->add($referentTag);
        }
    }

    public function removeReferentTag(ReferentTag $referentTag): void
    {
        $this->referentTags->remove($referentTag);
    }

    public function clearReferentTags(): void
    {
        $this->referentTags->clear();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("committeeUuid")
     * @JMS\Groups({"public", "event_read"})
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
     * @JMS\Groups({"public", "event_read"})
     */
    public function getEventCategoryName(): ?string
    {
        if (!$category = $this->getCategory()) {
            return null;
        }

        return $category->getName();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("tags")
     * @JMS\Groups({"public", "event_read"})
     */
    public function getReferentTagsCodes(): array
    {
        return array_map(function (ReferentTag $referentTag) {
            return $referentTag->getCode();
        }, $this->referentTags->toArray());
    }

    public function isReferentEvent(): bool
    {
        return null === $this->getCommittee();
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("organizerUuid")
     * @JMS\Groups({"public", "event_read"})
     */
    public function getOrganizerUuid(): ?string
    {
        if (!$organizer = $this->getOrganizer()) {
            return null;
        }

        return $organizer->getUuidAsString();
    }
}
