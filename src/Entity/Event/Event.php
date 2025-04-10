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
use App\Api\Filter\EventsDepartmentFilter;
use App\Api\Filter\InZoneOfScopeFilter;
use App\Api\Filter\MyCreatedEventsFilter;
use App\Api\Filter\MySubscribedEventsFilter;
use App\Api\Filter\OrderEventsBySubscriptionsFilter;
use App\Api\Provider\EventProvider;
use App\Collection\ZoneCollection;
use App\Controller\Api\Event\CancelEventController;
use App\Controller\Api\Event\SubscribeAsAdherentController;
use App\Controller\Api\Event\SubscribeAsAnonymousController;
use App\Controller\Api\UpdateImageController;
use App\Entity\AddressHolderInterface;
use App\Entity\Adherent;
use App\Entity\AdvancedImageTrait;
use App\Entity\AuthorInstanceInterface;
use App\Entity\AuthorInstanceTrait;
use App\Entity\Committee;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityNullablePostAddressTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\Geo\Zone;
use App\Entity\ImageExposeInterface;
use App\Entity\ImageFullManageableInterface;
use App\Entity\IndexableEntityInterface;
use App\Entity\NotificationObjectInterface;
use App\Entity\NullablePostAddress;
use App\Entity\Report\ReportableInterface;
use App\Entity\ZoneableEntityInterface;
use App\EntityListener\AlgoliaIndexListener;
use App\Event\EventVisibilityEnum;
use App\Geocoder\GeoPointInterface;
use App\JeMengage\Push\Command\EventReminderNotificationCommand;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\Normalizer\ImageExposeNormalizer;
use App\Report\ReportType;
use App\Repository\Event\EventRepository;
use App\Validator\AdherentInterests as AdherentInterestsConstraint;
use App\Validator\DateRange;
use App\Validator\EventCategory as AssertValidEventCategory;
use Cake\Chronos\Chronos;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: InZoneOfScopeFilter::class)]
#[ApiFilter(filterClass: MyCreatedEventsFilter::class)]
#[ApiFilter(filterClass: MySubscribedEventsFilter::class)]
#[ApiFilter(filterClass: OrderEventsBySubscriptionsFilter::class)]
#[ApiFilter(filterClass: EventsDepartmentFilter::class)]
#[ApiFilter(filterClass: DateFilter::class, properties: ['finishAt' => 'strictly_after'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['name' => 'partial', 'mode' => 'exact', 'beginAt' => 'start', 'status' => 'exact'])]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['createdAt', 'beginAt', 'finishAt'])]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/events/{uuid}',
            provider: EventProvider::class,
        ),
        new Get(
            uriTemplate: '/events/{uuid}',
            security: '!object.isForAdherent()',
            provider: EventProvider::class,
        ),
        new Put(
            uriTemplate: '/v3/events/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'events') and is_granted('CAN_MANAGE_EVENT', object)"
        ),
        new Delete(
            uriTemplate: '/v3/events/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'events') and is_granted('CAN_MANAGE_EVENT', object) and is_granted('CAN_DELETE_EVENT', object)"
        ),
        new HttpOperation(
            method: 'POST|DELETE',
            uriTemplate: '/v3/events/{uuid}/subscribe',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: SubscribeAsAdherentController::class,
            security: 'is_granted(\'ROLE_USER\')',
            deserialize: false
        ),
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/events/{uuid}/subscribe',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: SubscribeAsAnonymousController::class,
            deserialize: false
        ),
        new HttpOperation(
            method: 'POST|DELETE',
            uriTemplate: '/v3/events/{uuid}/image',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: UpdateImageController::class,
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'events') and is_granted('CAN_MANAGE_EVENT', request.attributes.get('data'))",
            deserialize: false
        ),
        new HttpOperation(
            method: 'PUT',
            uriTemplate: '/v3/events/{uuid}/cancel',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: CancelEventController::class,
            deserialize: false
        ),
        new GetCollection(
            uriTemplate: '/v3/events',
            normalizationContext: ['groups' => ['event_list_read', ImageExposeNormalizer::NORMALIZATION_GROUP]]
        ),
        new GetCollection(
            uriTemplate: '/events',
            normalizationContext: ['groups' => ['event_list_read', ImageExposeNormalizer::NORMALIZATION_GROUP]]
        ),
        new Post(
            uriTemplate: '/v3/events',
            denormalizationContext: ['groups' => ['event_write', 'event_write_creation']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'events')",
            validationContext: ['groups' => ['Default', 'api_put_validation', 'event_creation']]
        ),
    ],
    normalizationContext: ['groups' => ['event_read', ImageExposeNormalizer::NORMALIZATION_GROUP]],
    denormalizationContext: ['groups' => ['event_write']],
    order: ['beginAt' => 'ASC']
)]
#[AssertValidEventCategory]
#[DateRange(
    startDateField: 'beginAt',
    endDateField: 'finishAt',
    interval: '3 days',
    messageDate: 'committee.event.invalid_finish_date'
)]
#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\EntityListeners([AlgoliaIndexListener::class])]
#[ORM\Index(columns: ['begin_at'])]
#[ORM\Index(columns: ['finish_at'])]
#[ORM\Index(columns: ['status'])]
#[ORM\Table(name: '`events`')]
class Event implements ReportableInterface, GeoPointInterface, AddressHolderInterface, ZoneableEntityInterface, AuthorInstanceInterface, ImageExposeInterface, ImageFullManageableInterface, IndexableEntityInterface, NotificationObjectInterface
{
    use EntityIdentityTrait;
    use EntityNullablePostAddressTrait;
    use EntityZoneTrait;
    use EntityTimestampableTrait;
    use AdvancedImageTrait;
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
    #[ApiProperty(identifier: true, builtinTypes: [new Type(Type::BUILTIN_TYPE_STRING)])]
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
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 5, max: 100),
    ])]
    #[Groups(['event_read', 'event_write', 'event_write_limited', 'event_list_read'])]
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
    #[Gedmo\Slug(fields: ['beginAt', 'canonicalName'], updatable: false, dateFormat: 'Y-m-d')]
    #[Groups(['event_read', 'event_list_read'])]
    #[ORM\Column(length: 130, unique: true)]
    protected $slug;

    /**
     * @var string
     */
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Length(min: 10),
    ])]
    #[Groups(['event_read', 'event_write'])]
    #[ORM\Column(type: 'text')]
    protected $description;

    #[Groups(['event_read', 'event_write', 'event_write_limited', 'event_write_creation'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $jsonDescription = null;

    #[Groups(['event_read', 'event_write_creation'])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Committee::class, fetch: 'EAGER')]
    private $committee;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Timezone]
    #[Groups(['event_read', 'event_write', 'event_write_limited', 'event_list_read'])]
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

    #[Groups(['event_read', 'event_list_read'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $national = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $pushSentAt = null;

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
    #[Groups(['event_read', 'event_write', 'event_write_limited', 'event_list_read'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $capacity;

    #[Assert\Url]
    #[Groups(['event_read', 'event_write', 'event_write_limited', 'event_list_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private $visioUrl;

    #[AdherentInterestsConstraint]
    #[Groups(['event_write', 'event_write_limited'])]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $interests = [];

    /**
     * @var string|null
     */
    #[Assert\Choice(choices: self::MODES)]
    #[Groups(['event_read', 'event_write', 'event_write_limited', 'event_list_read'])]
    #[ORM\Column(nullable: true)]
    private $mode;

    /**
     * @var EventCategoryInterface|EventCategory|null
     */
    #[Groups(['event_read', 'event_list_read', 'event_write', 'event_write_limited', 'event_write_creation'])]
    #[ORM\ManyToOne(targetEntity: EventCategory::class)]
    protected $category;

    /**
     * @var NullablePostAddress
     */
    #[Assert\Valid]
    #[Groups(['event_read', 'event_write', 'event_write_limited', 'event_list_read'])]
    #[ORM\Embedded(class: NullablePostAddress::class, columnPrefix: 'address_')]
    protected $postAddress;

    #[Groups(['event_read', 'event_write', 'event_write_limited', 'event_list_read'])]
    #[ORM\Column(enumType: EventVisibilityEnum::class, options: ['default' => 'public'])]
    public EventVisibilityEnum $visibility = EventVisibilityEnum::PUBLIC;

    #[Assert\Url]
    #[Groups(['event_read', 'event_write', 'event_write_limited', 'event_list_read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $liveUrl = null;

    #[Groups(['event_write_creation'])]
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $sendInvitationEmail = true;

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

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getCategoryName(): ?string
    {
        return $this->category?->getName();
    }

    public function __toString(): string
    {
        return $this->getName();
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

    #[SerializedName('is_national')]
    public function isNational(): bool
    {
        return $this->national;
    }

    public function setNational(bool $national): void
    {
        $this->national = $national;
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

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(?Committee $committee): void
    {
        $this->committee = $committee;
    }

    public function getCommitteeUuid(): ?string
    {
        if (!$committee = $this->getCommittee()) {
            return null;
        }

        return $committee->getUuidAsString();
    }

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
        return Event::MODE_ONLINE === $this->mode;
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
        bool $electoral = false,
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
        return (bool) $this->committee;
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

    public function isPublic(): bool
    {
        return EventVisibilityEnum::PUBLIC === $this->visibility;
    }

    public function isForAdherent(): bool
    {
        return \in_array($this->visibility, [EventVisibilityEnum::ADHERENT, EventVisibilityEnum::ADHERENT_DUES], true);
    }

    public function isNotificationEnabled(SendNotificationCommandInterface $command): bool
    {
        if ($command instanceof EventReminderNotificationCommand) {
            return $this->isPublished() && !$this->isReminded();
        }

        return $this->isPublished();
    }

    public function handleNotificationSent(SendNotificationCommandInterface $command): void
    {
        if ($command instanceof EventReminderNotificationCommand) {
            $this->setReminded(true);
        }
    }

    public function isLivePlayerEnabled(): bool
    {
        return str_starts_with($this->liveUrl ?? '', 'https://vimeo.com/');
    }
}
