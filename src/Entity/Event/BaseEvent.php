<?php

namespace App\Entity\Event;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Address\AddressInterface;
use App\Address\GeoCoder;
use App\Api\Filter\EventScopeFilter;
use App\Api\Filter\EventsGroupSourceFilter;
use App\Api\Filter\EventsZipCodeFilter;
use App\Api\Filter\MyCreatedEventsFilter;
use App\Api\Filter\MySubscribedEventsFilter;
use App\Api\Filter\OrderEventsBySubscriptionsFilter;
use App\Api\Filter\OrderFilter;
use App\Entity\AddressHolderInterface;
use App\Entity\Adherent;
use App\Entity\AuthorInterface;
use App\Entity\EntityCrudTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityPostAddressTrait;
use App\Entity\EntityReferentTagTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\ExposedImageOwnerInterface;
use App\Entity\ExposedObjectInterface;
use App\Entity\ImageTrait;
use App\Entity\IndexableEntityInterface;
use App\Entity\PostAddress;
use App\Entity\ReferentTag;
use App\Entity\ReferentTaggableEntity;
use App\Entity\Report\ReportableInterface;
use App\Entity\ZoneableEntity;
use App\Event\EventTypeEnum;
use App\Firebase\DynamicLinks\DynamicLinkObjectInterface;
use App\Firebase\DynamicLinks\DynamicLinkObjectTrait;
use App\Geocoder\GeoPointInterface;
use App\Report\ReportType;
use App\Validator\AddressInScopeZones as AssertAddressInScopeZones;
use App\Validator\AdherentInterests as AdherentInterestsConstraint;
use App\Validator\DateRange;
use App\Validator\EventCategory as AssertValidEventCategory;
use Cake\Chronos\Chronos;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Event\BaseEventRepository")
 * @ORM\Table(
 *     name="events",
 *     indexes={
 *         @ORM\Index(columns={"begin_at"}),
 *         @ORM\Index(columns={"finish_at"}),
 *         @ORM\Index(columns={"status"})
 *     }
 * )
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="zones",
 *         joinTable=@ORM\JoinTable(name="event_zone")
 *     )
 * })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     EventTypeEnum::TYPE_DEFAULT: "DefaultEvent",
 *     EventTypeEnum::TYPE_COMMITTEE: "CommitteeEvent",
 *     EventTypeEnum::TYPE_COALITION: "CoalitionEvent",
 *     EventTypeEnum::TYPE_CAUSE: "CauseEvent",
 *     EventTypeEnum::TYPE_INSTITUTIONAL: "InstitutionalEvent",
 *     EventTypeEnum::TYPE_MUNICIPAL: "MunicipalEvent",
 * })
 *
 * @DateRange(
 *     startDateField="beginAt",
 *     endDateField="finishAt",
 *     interval="3 days",
 *     messageDate="committee.event.invalid_finish_date"
 * )
 *
 * @ApiResource(
 *     attributes={
 *         "order": {"beginAt": "ASC"},
 *         "denormalization_context": {"groups": {"event_write"}},
 *         "normalization_context": {
 *             "groups": {"event_read", "image_owner_exposed"}
 *         },
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/events/{id}",
 *             "normalization_context": {
 *                 "groups": {"event_read", "image_owner_exposed", "with_user_registration"}
 *             },
 *         },
 *         "get_public": {
 *             "method": "GET",
 *             "path": "/events/{id}",
 *         },
 *         "put": {
 *             "path": "/v3/events/{id}",
 *             "access_control": "is_granted('CAN_MANAGE_EVENT', object)",
 *             "requirements": {"id": "%pattern_uuid%"},
 *         },
 *         "delete": {
 *             "path": "/v3/events/{id}",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "access_control": "is_granted('CAN_MANAGE_EVENT', object) and is_granted('CAN_DELETE_EVENT', object)",
 *             "swagger_context": {
 *                 "parameters": {
 *                     {
 *                         "name": "id",
 *                         "in": "path",
 *                         "type": "uuid",
 *                         "description": "The UUID of the Event.",
 *                         "example": "de7982c4-3729-4f9d-9587-376df25354c3",
 *                     },
 *                 },
 *             },
 *         },
 *         "subscribe": {
 *             "method": "POST|DELETE",
 *             "path": "/v3/events/{uuid}/subscribe",
 *             "access_control": "is_granted('ROLE_USER')",
 *             "defaults": {"_api_receive": false},
 *             "controller": "App\Controller\Api\Event\SubscribeAsAdherentController",
 *             "requirements": {"uuid": "%pattern_uuid%"}
 *         },
 *         "subscribe_anonymous": {
 *             "method": "POST",
 *             "path": "/events/{uuid}/subscribe",
 *             "access_control": "is_granted('IS_AUTHENTICATED_ANONYMOUSLY')",
 *             "defaults": {"_api_receive": false},
 *             "controller": "App\Controller\Api\Event\SubscribeAsAnonymousController",
 *             "requirements": {"uuid": "%pattern_uuid%"}
 *         },
 *         "update_image": {
 *             "method": "POST|DELETE",
 *             "path": "/v3/events/{uuid}/image",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "defaults": {"_api_receive": false},
 *             "controller": "App\Controller\Api\EventImageController",
 *         },
 *         "cancel": {
 *             "path": "/v3/events/{uuid}/cancel",
 *             "method": "PUT",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "defaults": {"_api_receive": false},
 *             "controller": "App\Controller\Api\Event\CancelEventController",
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "path": "/v3/events",
 *             "normalization_context": {
 *                 "groups": {"event_list_read", "image_owner_exposed", "with_user_registration"}
 *             },
 *         },
 *         "get_public": {
 *             "method": "GET",
 *             "path": "/events",
 *             "normalization_context": {
 *                 "groups": {"event_list_read", "image_owner_exposed"}
 *             },
 *         },
 *         "post": {
 *             "access_control": "is_granted('ROLE_USER')",
 *             "path": "/v3/events",
 *             "validation_groups": {"Default", "api_put_validation", "event_creation"},
 *         },
 *     },
 * )
 *
 * @ApiFilter(EventScopeFilter::class)
 * @ApiFilter(EventsGroupSourceFilter::class)
 * @ApiFilter(MyCreatedEventsFilter::class)
 * @ApiFilter(MySubscribedEventsFilter::class)
 * @ApiFilter(OrderEventsBySubscriptionsFilter::class)
 * @ApiFilter(EventsZipCodeFilter::class)
 * @ApiFilter(DateFilter::class, properties={"finishAt": "strictly_after"})
 * @ApiFilter(SearchFilter::class, properties={
 *     "name": "partial",
 *     "mode": "exact",
 *     "beginAt": "start",
 * })
 * @ApiFilter(OrderFilter::class, properties={"createdAt", "beginAt", "finishAt"})
 *
 * @ORM\EntityListeners({
 *     "App\EntityListener\DynamicLinkListener",
 *     "App\EntityListener\AlgoliaIndexListener",
 * })
 *
 * @AssertValidEventCategory
 */
abstract class BaseEvent implements ReportableInterface, GeoPointInterface, ReferentTaggableEntity, AddressHolderInterface, ZoneableEntity, AuthorInterface, ExposedImageOwnerInterface, IndexableEntityInterface, DynamicLinkObjectInterface, ExposedObjectInterface
{
    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityPostAddressTrait;
    use EntityReferentTagTrait;
    use EntityZoneTrait;
    use EntityTimestampableTrait;
    use ImageTrait;
    use DynamicLinkObjectTrait;

    public const STATUS_SCHEDULED = 'SCHEDULED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_CANCELLED,
    ];

    public const ACTIVE_STATUSES = [
        self::STATUS_SCHEDULED,
    ];

    public const MODE_ONLINE = 'online';
    public const MODE_MEETING = 'meeting';

    public const MODES = [
        self::MODE_ONLINE,
        self::MODE_MEETING,
    ];

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid", unique=true)
     *
     * @SymfonySerializer\Groups({"event_read", "event_sync", "event_list_read"})
     *
     * @ApiProperty(identifier=true)
     */
    protected $uuid;

    /**
     * @var Collection|ReferentTag[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag")
     * @ORM\JoinTable(
     *     name="event_referent_tag",
     *     joinColumns={
     *         @ORM\JoinColumn(name="event_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="referent_tag_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     */
    protected $referentTags;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100)
     *
     * @SymfonySerializer\Groups({"event_read", "event_sync", "event_write", "event_list_read"})
     *
     * @Assert\NotBlank
     * @Assert\Length(min=5, max=100)
     */
    protected $name;

    /**
     * The event canonical name.
     *
     * @var string|null
     *
     * @Assert\NotBlank
     * @ORM\Column(length=100)
     */
    protected $canonicalName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=130, unique=true)
     * @Gedmo\Slug(
     *     fields={"beginAt", "canonicalName"},
     *     dateFormat="Y-m-d",
     *     handlers={@Gedmo\SlugHandler(class="App\Event\UniqueEventNameHandler")}
     * )
     *
     * @SymfonySerializer\Groups({"event_read", "event_sync"})
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @SymfonySerializer\Groups({"event_read", "event_write"})
     *
     * @Assert\NotBlank
     * @Assert\Length(min=10)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @SymfonySerializer\Groups({"event_read", "event_sync", "event_write", "event_list_read"})
     *
     * @Assert\NotBlank
     * @Assert\Timezone
     */
    protected $timeZone = GeoCoder::DEFAULT_TIME_ZONE;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime")
     *
     * @SymfonySerializer\Groups({"event_read", "event_sync", "event_write", "event_list_read"})
     *
     * @Assert\NotBlank
     */
    protected $beginAt;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime")
     *
     * @SymfonySerializer\Groups({"event_read", "event_sync", "event_write", "event_list_read"})
     *
     * @Assert\NotBlank
     * @Assert\Expression("!value or value > this.getBeginAt()", message="committee.event.invalid_date_range")
     */
    protected $finishAt;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="RESTRICT")
     *
     * @Assert\NotBlank
     *
     * @SymfonySerializer\Groups({"event_read", "event_list_read"})
     */
    protected $organizer;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @SymfonySerializer\Groups({"event_read", "event_sync", "event_list_read"})
     */
    protected $participantsCount = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(length=20)
     *
     * @SymfonySerializer\Groups({"event_read", "event_sync", "event_list_read"})
     */
    protected $status = self::STATUS_SCHEDULED;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": true})
     */
    protected $published = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    protected $reminded = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @SymfonySerializer\Groups({"event_write", "event_list_read_extended", "event_read_extended"})
     */
    private $private = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @SymfonySerializer\Groups({"event_write", "event_list_read_extended", "event_read_extended"})
     */
    private $electoral = false;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @SymfonySerializer\Groups({"event_read", "event_sync", "event_write", "event_list_read"})
     *
     * @Assert\GreaterThan("0", message="committee.event.invalid_capacity")
     */
    protected $capacity;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Url
     *
     * @SymfonySerializer\Groups({"event_read", "event_write", "event_list_read_extended"})
     */
    private $visioUrl;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @SymfonySerializer\Groups({"event_write"})
     *
     * @AdherentInterestsConstraint
     */
    private $interests = [];

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @SymfonySerializer\Groups({"event_read", "event_write", "event_list_read"})
     *
     * @Assert\Choice(choices=self::MODES)
     */
    private $mode;

    /**
     * @ORM\Embedded(class="App\Entity\PostAddress", columnPrefix="address_")
     *
     * @var PostAddress
     *
     * @SymfonySerializer\Groups({"event_read", "event_write", "event_list_read"})
     *
     * @AssertAddressInScopeZones
     */
    protected $postAddress;

    protected $category;

    public function getCategory(): ?EventCategoryInterface
    {
        return $this->category;
    }

    public function setCategory(?EventCategoryInterface $category): void
    {
        $this->category = $category;
    }

    public function getCategoryName(): ?string
    {
        return $this->category ? $this->category->getName() : null;
    }

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();

        $this->referentTags = new ArrayCollection();
        $this->zones = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?: '';
    }

    protected static function canonicalize(string $name): string
    {
        return mb_strtolower($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCanonicalName(): ?string
    {
        return $this->canonicalName;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getBeginAt(): ?\DateTimeInterface
    {
        return $this->beginAt;
    }

    public function setBeginAt(?\DateTimeInterface $beginAt): void
    {
        $this->beginAt = $beginAt;
    }

    public function getLocalBeginAt(): \DateTimeInterface
    {
        return (clone $this->beginAt)->setTimezone(new \DateTimeZone($this->getTimeZone()));
    }

    public function getFinishAt(): ?\DateTimeInterface
    {
        return $this->finishAt;
    }

    public function setFinishAt(?\DateTimeInterface $finishAt): void
    {
        $this->finishAt = $finishAt;
    }

    /**
     * @SymfonySerializer\Groups({"event_list_read"})
     */
    public function getLocalFinishAt(): \DateTimeInterface
    {
        return (clone $this->finishAt)->setTimezone(new \DateTimeZone($this->getTimeZone()));
    }

    public function setOrganizer(?Adherent $organizer): void
    {
        $this->organizer = $organizer;
    }

    public function getOrganizer(): ?Adherent
    {
        return $this->organizer;
    }

    public function getOrganizerName(): ?string
    {
        return $this->organizer ? $this->organizer->getFirstName() : '';
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getParticipantsCount(): int
    {
        return $this->participantsCount;
    }

    public function incrementParticipantsCount(int $increment = 1): void
    {
        $this->participantsCount += $increment;
    }

    public function decrementParticipantsCount(int $increment = 1): void
    {
        $this->participantsCount = max($this->participantsCount - $increment, 0);
    }

    public function setName(string $name): void
    {
        $this->name = ucfirst($name);
        $this->canonicalName = static::canonicalize($name);
    }

    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    public function setTimeZone(string $timeZone): void
    {
        $this->timeZone = $timeZone;
    }

    public function isFinished(): bool
    {
        $finishAt = new \DateTimeImmutable(
            $this->finishAt->format('Y-m-d H:i'),
            $timezone = new \DateTimeZone($this->timeZone ?? GeoCoder::DEFAULT_TIME_ZONE)
        );
        $now = new Chronos('now');

        return $finishAt < $now->setTimezone($timezone);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        if (!\in_array($status, self::STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid status "%" given.', $status);
        }

        $this->status = $status;
    }

    public function publish(): void
    {
        $this->published = true;
    }

    public function cancel(): void
    {
        $this->status = self::STATUS_CANCELLED;
    }

    public function isActive(): bool
    {
        return \in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isCancelled(): bool
    {
        return self::STATUS_CANCELLED === $this->status;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function isFull(): bool
    {
        if (!$this->capacity) {
            return false;
        }

        return $this->participantsCount >= $this->capacity;
    }

    abstract public function getType(): string;

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->uuid);
    }

    public function getUuidAsString(): string
    {
        return $this->getUuid()->toString();
    }

    public function getPostAddress(): ?AddressInterface
    {
        return $this->postAddress;
    }

    public function setAuthor(Adherent $adherent): void
    {
        $this->organizer = $adherent;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->organizer;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): void
    {
        $this->mode = $mode;
    }

    public function isOnline(): bool
    {
        return BaseEvent::MODE_ONLINE === $this->mode;
    }

    public function getVisioUrl(): ?string
    {
        return $this->visioUrl;
    }

    public function setVisioUrl(?string $visioUrl): void
    {
        $this->visioUrl = $visioUrl;
    }

    public function getImagePath(): string
    {
        return $this->imageName ? sprintf('images/events/%s', $this->getImageName()) : '';
    }

    public function getNormalizationGroups(): array
    {
        return ['event_read'];
    }

    public function getExposedRouteName(): string
    {
        return 'app_committee_event_show';
    }

    public function getExposedRouteParams(): array
    {
        return ['slug' => $this->slug];
    }

    public function update(
        string $name,
        string $description,
        PostAddress $address,
        string $timeZone,
        \DateTimeInterface $beginAt,
        \DateTimeInterface $finishAt,
        ?string $visioUrl = null,
        int $capacity = null,
        bool $private = false,
        bool $electoral = false
    ): void {
        $this->setName($name);
        $this->capacity = $capacity;
        $this->timeZone = $timeZone;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->description = $description;
        $this->private = $private;
        $this->electoral = $electoral;
        $this->setVisioUrl($visioUrl);

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }
    }

    public function getReportType(): string
    {
        return ReportType::COMMUNITY_EVENT;
    }

    public function isCoalitionsEvent(): bool
    {
        return false;
    }

    public function needNotifyForRegistration(): bool
    {
        return false;
    }

    public function needNotifyForCancellation(): bool
    {
        return false;
    }

    public function isReminded(): bool
    {
        return $this->reminded;
    }

    public function setReminded(bool $reminded): void
    {
        $this->reminded = $reminded;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }

    public function isElectoral(): bool
    {
        return $this->electoral;
    }

    public function setElectoral(bool $electoral): void
    {
        $this->electoral = $electoral;
    }

    public function isIndexable(): bool
    {
        return $this->isPublished() && $this->isGeocoded() && 'FR' === $this->getCountry();
    }

    public function getIndexOptions(): array
    {
        return [];
    }

    public function getDynamicLinkPath(): string
    {
        return sprintf('/events/%s', $this->getUuid());
    }

    public function withSocialMeta(): bool
    {
        return true;
    }

    public function getSocialTitle(): string
    {
        return $this->getName();
    }

    public function getSocialDescription(): string
    {
        return $this->getDescription();
    }
}
