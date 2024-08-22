<?php

namespace App\Entity\Event;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Address\AddressInterface;
use App\Address\GeoCoder;
use App\Api\Filter\EventsZipCodeFilter;
use App\Api\Filter\InZoneOfScopeFilter;
use App\Api\Filter\MyCreatedEventsFilter;
use App\Api\Filter\MySubscribedEventsFilter;
use App\Api\Filter\OrderEventsBySubscriptionsFilter;
use App\Collection\ZoneCollection;
use App\Controller\Api\Event\CancelEventController;
use App\Controller\Api\Event\SubscribeAsAdherentController;
use App\Controller\Api\Event\SubscribeAsAnonymousController;
use App\Controller\Api\UpdateImageController;
use App\Entity\AddressHolderInterface;
use App\Entity\Adherent;
use App\Entity\AuthorInstanceInterface;
use App\Entity\AuthorInstanceTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityNullablePostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\ExposedImageOwnerInterface;
use App\Entity\ExposedObjectInterface;
use App\Entity\Geo\Zone;
use App\Entity\ImageTrait;
use App\Entity\IndexableEntityInterface;
use App\Entity\NullablePostAddress;
use App\Entity\Report\ReportableInterface;
use App\Entity\ZoneableEntity;
use App\EntityListener\AlgoliaIndexListener;
use App\EntityListener\DynamicLinkListener;
use App\Event\EventTypeEnum;
use App\Event\EventVisibilityEnum;
use App\Event\UniqueEventNameHandler;
use App\Firebase\DynamicLinks\DynamicLinkObjectInterface;
use App\Firebase\DynamicLinks\DynamicLinkObjectTrait;
use App\Geocoder\GeoPointInterface;
use App\Normalizer\ImageOwnerExposedNormalizer;
use App\Report\ReportType;
use App\Repository\Event\BaseEventRepository;
use App\Validator\AdherentInterests as AdherentInterestsConstraint;
use App\Validator\DateRange;
use App\Validator\EventCategory as AssertValidEventCategory;
use Cake\Chronos\Chronos;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @DateRange(
 *     startDateField="beginAt",
 *     endDateField="finishAt",
 *     interval="3 days",
 *     messageDate="committee.event.invalid_finish_date"
 * )
 *
 * @AssertValidEventCategory
 */
#[ApiFilter(filterClass: InZoneOfScopeFilter::class)]
#[ApiFilter(filterClass: MyCreatedEventsFilter::class)]
#[ApiFilter(filterClass: MySubscribedEventsFilter::class)]
#[ApiFilter(filterClass: OrderEventsBySubscriptionsFilter::class)]
#[ApiFilter(filterClass: EventsZipCodeFilter::class)]
#[ApiFilter(filterClass: DateFilter::class, properties: ['finishAt' => 'strictly_after'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['name' => 'partial', 'mode' => 'exact', 'beginAt' => 'start', 'status' => 'exact'])]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['createdAt', 'beginAt', 'finishAt'])]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/events/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
        ),
        new Get(
            uriTemplate: '/events/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%']
        ),
        new Put(
            uriTemplate: '/v3/events/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: 'is_granted(\'ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN\') and is_granted(\'CAN_MANAGE_EVENT\', object)'
        ),
        new Delete(
            uriTemplate: '/v3/events/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            openapiContext: ['parameters' => [['name' => 'uuid', 'in' => 'path', 'type' => 'uuid', 'description' => 'The UUID of the Event.', 'example' => 'de7982c4-3729-4f9d-9587-376df25354c3']]],
            security: 'is_granted(\'ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN\') and is_granted(\'CAN_MANAGE_EVENT\', object) and is_granted(\'CAN_DELETE_EVENT\', object)'
        ),
        new HttpOperation(
            method: 'POST|DELETE',
            uriTemplate: '/v3/events/{uuid}/subscribe',
            defaults: ['_api_receive' => false],
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: SubscribeAsAdherentController::class,
            security: 'is_granted(\'ROLE_USER\')'
        ),
        new Post(
            uriTemplate: '/events/{uuid}/subscribe',
            defaults: ['_api_receive' => false],
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: SubscribeAsAnonymousController::class
        ),
        new HttpOperation(
            method: 'POST|DELETE',
            uriTemplate: '/v3/events/{uuid}/image',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: UpdateImageController::class,
            security: "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('CAN_MANAGE_EVENT', request.attributes.get('data'))",
            deserialize: false
        ),
        new Put(
            uriTemplate: '/v3/events/{uuid}/cancel',
            defaults: ['_api_receive' => false],
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: CancelEventController::class
        ),
        new GetCollection(
            uriTemplate: '/v3/events',
            normalizationContext: ['groups' => ['event_list_read', ImageOwnerExposedNormalizer::NORMALIZATION_GROUP]]
        ),
        new GetCollection(
            uriTemplate: '/events',
            normalizationContext: ['groups' => ['event_list_read', ImageOwnerExposedNormalizer::NORMALIZATION_GROUP]]
        ),
        new Post(
            uriTemplate: '/v3/events',
            denormalizationContext: ['groups' => ['event_write', 'event_write_creation']],
            security: 'is_granted(\'ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN\') and is_granted(\'IS_FEATURE_GRANTED\', \'events\')',
            validationContext: ['groups' => ['Default', 'api_put_validation', 'event_creation']]
        ),
    ],
    normalizationContext: ['groups' => ['event_read', ImageOwnerExposedNormalizer::NORMALIZATION_GROUP]],
    denormalizationContext: ['groups' => ['event_write']],
    order: ['beginAt' => 'ASC']
)]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([EventTypeEnum::TYPE_DEFAULT => DefaultEvent::class, EventTypeEnum::TYPE_COMMITTEE => CommitteeEvent::class])]
#[ORM\Entity(repositoryClass: BaseEventRepository::class)]
#[ORM\EntityListeners([DynamicLinkListener::class, AlgoliaIndexListener::class])]
#[ORM\Index(columns: ['begin_at'])]
#[ORM\Index(columns: ['finish_at'])]
#[ORM\Index(columns: ['status'])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\Table(name: '`events`')]
abstract class BaseEvent implements ReportableInterface, GeoPointInterface, AddressHolderInterface, ZoneableEntity, AuthorInstanceInterface, ExposedImageOwnerInterface, IndexableEntityInterface, DynamicLinkObjectInterface, ExposedObjectInterface
{
    use EntityIdentityTrait;
    use EntityNullablePostAddressTrait;
    use EntityZoneTrait;
    use EntityTimestampableTrait;
    use ImageTrait;
    use DynamicLinkObjectTrait;
    use AuthorInstanceTrait;

    public const STATUS_SCHEDULED = 'SCHEDULED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_CANCELLED,
    ];

    public const MODE_ONLINE = 'online';
    public const MODE_MEETING = 'meeting';

    public const MODES = [
        self::MODE_ONLINE,
        self::MODE_MEETING,
    ];

    /**
     * @var UuidInterface
     */
    #[ApiProperty(identifier: true)]
    #[Groups(['event_read', 'event_list_read'])]
    #[ORM\Column(type: 'uuid', unique: true)]
    protected $uuid;

    /**
     * @var ZoneCollection|Zone[]
     */
    #[ORM\JoinTable(name: 'event_zone')]
    #[ORM\ManyToMany(targetEntity: Zone::class, cascade: ['persist'])]
    protected Collection $zones;

    /**
     * @var string|null
     */
    #[Assert\Length(min: 5, max: 100, options: ['allowEmptyString' => true])]
    #[Assert\NotBlank]
    #[Groups(['event_read', 'event_write', 'event_list_read'])]
    #[ORM\Column(length: 100)]
    protected $name;

    /**
     * The event canonical name.
     *
     * @var string|null
     */
    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    protected $canonicalName;

    /**
     * @var string|null
     */
    #[Gedmo\Slug(fields: ['beginAt', 'canonicalName'], handlers: [new Gedmo\SlugHandler(class: UniqueEventNameHandler::class)], dateFormat: 'Y-m-d')]
    #[Groups(['event_read'])]
    #[ORM\Column(length: 130, unique: true)]
    protected $slug;

    /**
     * @var string
     */
    #[Assert\Length(min: 10, options: ['allowEmptyString' => true])]
    #[Assert\NotBlank]
    #[Groups(['event_read', 'event_write'])]
    #[ORM\Column(type: 'text')]
    protected $description;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Timezone]
    #[Groups(['event_read', 'event_write', 'event_list_read'])]
    #[ORM\Column(length: 50)]
    protected $timeZone = GeoCoder::DEFAULT_TIME_ZONE;

    /**
     * @var \DateTimeInterface|null
     */
    #[Assert\NotBlank]
    #[Groups(['event_read', 'event_write', 'event_list_read'])]
    #[ORM\Column(type: 'datetime')]
    protected $beginAt;

    /**
     * @var \DateTimeInterface|null
     */
    #[Assert\Expression('!value or value > this.getBeginAt()', message: 'committee.event.invalid_date_range')]
    #[Assert\NotBlank]
    #[Groups(['event_read', 'event_write', 'event_list_read'])]
    #[ORM\Column(type: 'datetime')]
    protected $finishAt;

    /**
     * @var int
     */
    #[Groups(['event_read', 'event_list_read'])]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    protected $participantsCount = 0;

    /**
     * @var string|null
     */
    #[Groups(['event_read', 'event_list_read'])]
    #[ORM\Column(length: 20)]
    protected $status = self::STATUS_SCHEDULED;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    protected $published = true;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    protected $reminded = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $electoral = false;

    /**
     * @var int|null
     */
    #[Assert\GreaterThan('0', message: 'committee.event.invalid_capacity')]
    #[Groups(['event_read', 'event_write', 'event_list_read'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $capacity;

    #[Assert\Url]
    #[Groups(['event_read', 'event_write', 'event_list_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $visioUrl;

    /**
     * @AdherentInterestsConstraint
     */
    #[Groups(['event_write'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $interests = [];

    /**
     * @var string|null
     */
    #[Assert\Choice(choices: self::MODES)]
    #[Groups(['event_read', 'event_write', 'event_list_read'])]
    #[ORM\Column(nullable: true)]
    private $mode;

    /**
     * @var EventCategoryInterface|EventCategory|null
     */
    #[Groups(['event_read', 'event_list_read', 'event_write'])]
    #[ORM\ManyToOne(targetEntity: EventCategory::class)]
    protected $category;

    /**
     * @var NullablePostAddress
     */
    #[Assert\Valid]
    #[Groups(['event_read', 'event_write', 'event_list_read'])]
    #[ORM\Embedded(class: NullablePostAddress::class, columnPrefix: 'address_')]
    protected $postAddress;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $renaissanceEvent = false;

    #[Groups(['event_read', 'event_write', 'event_list_read'])]
    #[ORM\Column(enumType: EventVisibilityEnum::class, options: ['default' => 'public'])]
    public EventVisibilityEnum $visibility = EventVisibilityEnum::PUBLIC;

    #[Groups(['event_read', 'event_write', 'event_list_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $liveUrl = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->zones = new ZoneCollection();
    }

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
        return $this->category?->getName();
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
        return $this->name ?? '';
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

    #[Groups(['event_read', 'event_list_read'])]
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

    #[Groups(['event_read', 'event_list_read'])]
    public function getLocalFinishAt(): \DateTimeInterface
    {
        return (clone $this->finishAt)->setTimezone(new \DateTimeZone($this->getTimeZone()));
    }

    #[Groups(['event_read', 'event_list_read'])]
    public function getOrganizer(): ?Adherent
    {
        return $this->getAuthor();
    }

    public function getOrganizerName(): ?string
    {
        return $this->author ? $this->author->getFirstName() : '';
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
        return self::STATUS_SCHEDULED === $this->status;
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
        return $this->imageName ? \sprintf('images/events/%s', $this->getImageName()) : '';
    }

    public function getNormalizationGroups(): array
    {
        return ['event_read'];
    }

    public function getExposedRouteName(): string
    {
        return 'app_renaissance_event_show';
    }

    public function getExposedRouteParams(): array
    {
        return ['slug' => $this->slug];
    }

    public function update(
        string $name,
        string $description,
        AddressInterface $address,
        string $timeZone,
        \DateTimeInterface $beginAt,
        \DateTimeInterface $finishAt,
        ?string $visioUrl = null,
        ?int $capacity = null,
        bool $private = false,
        bool $electoral = false
    ): void {
        $this->setName($name);
        $this->capacity = $capacity;
        $this->timeZone = $timeZone;
        $this->beginAt = $beginAt;
        $this->finishAt = $finishAt;
        $this->description = $description;
        $this->setPrivate($private);
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

    public function needNotifyForRegistration(): bool
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
        return EventVisibilityEnum::PRIVATE === $this->visibility;
    }

    public function setPrivate(bool $private): void
    {
        if ($private) {
            $this->visibility = EventVisibilityEnum::PRIVATE;
        } else {
            $this->visibility = EventVisibilityEnum::PUBLIC;
        }
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
        return $this->isPublished() && !$this->isCancelled();
    }

    public function getIndexOptions(): array
    {
        return [];
    }

    public function getDynamicLinkPath(): string
    {
        return \sprintf('/events/%s', $this->getUuid());
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

    public function isRenaissanceEvent(): bool
    {
        return $this->renaissanceEvent;
    }

    public function setRenaissanceEvent(bool $renaissanceEvent): void
    {
        $this->renaissanceEvent = $renaissanceEvent;
    }

    public function isPublic(): bool
    {
        return EventVisibilityEnum::PUBLIC === $this->visibility;
    }

    public function isForAdherent(): bool
    {
        return \in_array($this->visibility, [EventVisibilityEnum::ADHERENT, EventVisibilityEnum::ADHERENT_DUES], true);
    }
}
