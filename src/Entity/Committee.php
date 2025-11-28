<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Address\AddressInterface;
use App\AdherentMessage\StaticSegmentInterface;
use App\Api\Filter\InZoneOfScopeFilter;
use App\Collection\ZoneCollection;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\Geo\Zone;
use App\Entity\Report\ReportableInterface;
use App\Entity\VotingPlatform\Designation\ElectionEntityInterface;
use App\Entity\VotingPlatform\Designation\EntityElectionHelperTrait;
use App\Geocoder\GeoPointInterface;
use App\Normalizer\ImageExposeNormalizer;
use App\Report\ReportType;
use App\Repository\CommitteeRepository;
use App\Validator\ZoneType as AssertZoneType;
use App\ValueObject\Genders;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: InZoneOfScopeFilter::class)]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/committees/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['committee:list', 'committee:read', ImageExposeNormalizer::NORMALIZATION_GROUP]],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'committee') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object)"
        ),
        new Put(
            uriTemplate: '/committees/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['committee:list', 'committee:read', ImageExposeNormalizer::NORMALIZATION_GROUP]],
            denormalizationContext: ['groups' => ['committee:write']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'committee') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object)"
        ),
        new Delete(
            uriTemplate: '/committees/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'committee') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object)"
        ),
        new Put(
            uriTemplate: '/committees/{uuid}/animator',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['committee:list', 'committee:read', ImageExposeNormalizer::NORMALIZATION_GROUP]],
            denormalizationContext: ['groups' => ['committee:update_animator']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'committee') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object)"
        ),
        new GetCollection(),
        new Post(denormalizationContext: ['groups' => ['committee:write']]),
    ],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['committee:list', ImageExposeNormalizer::NORMALIZATION_GROUP]],
    validationContext: ['groups' => ['api_committee_edition']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'committee')"
)]
#[ORM\Entity(repositoryClass: CommitteeRepository::class)]
#[ORM\Index(columns: ['status'])]
#[ORM\Table(name: 'committees')]
class Committee implements StaticSegmentInterface, AddressHolderInterface, ZoneableEntityInterface, EntityAdherentBlameableInterface, GeoPointInterface, ReportableInterface, EntityAdministratorBlameableInterface
{
    use EntityNullablePostAddressTrait;
    use EntityZoneTrait;
    use EntityElectionHelperTrait;
    use StaticSegmentTrait;
    use EntityAdherentBlameableTrait;
    use EntityAdministratorBlameableTrait;
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityNameSlugTrait;

    public const APPROVED = 'APPROVED';
    public const PENDING = 'PENDING';
    public const REFUSED = 'REFUSED';
    public const CLOSED = 'CLOSED';

    public const BLOCKED_STATUSES = [
        self::CLOSED,
        self::REFUSED,
    ];

    /**
     * The group current status.
     */
    #[ORM\Column(length: 20)]
    protected $status = self::APPROVED;

    /**
     * The timestamp when an administrator approved this group.
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $approvedAt;

    /**
     * The timestamp when an administrator refused this group.
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $refusedAt;

    /**
     * The adherent UUID who created this group.
     */
    #[ORM\Column(type: 'uuid', nullable: true)]
    private $createdBy;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $closedAt;

    #[ORM\Column(type: 'phone_number', nullable: true)]
    private $phone;

    /**
     * The cached number of members (followers and hosts/administrators).
     */
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private int $membersCount = 0;

    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private int $adherentsCount = 0;

    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private int $sympathizersCount = 0;

    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private int $membersEmCount = 0;

    /**
     * The group description.
     */
    #[Groups(['committee:list', 'committee:write', 'committee:write_limited', 'profile_read'])]
    #[ORM\Column(type: 'text')]
    private $description;

    /**
     * The committee Facebook page URL.
     */
    #[ORM\Column(nullable: true)]
    public ?string $facebookPageUrl = null;

    /**
     * The committee Twitter nickname.
     */
    #[ORM\Column(nullable: true)]
    public ?string $twitterNickname = null;

    /**
     * Is also used to block address modification.
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $nameLocked = false;

    /**
     * @var CommitteeElection[]
     */
    #[ORM\OneToMany(mappedBy: 'committee', targetEntity: CommitteeElection::class, cascade: ['all'], orphanRemoval: true)]
    private $committeeElections;

    /**
     * A cached list of the hosts (for admin).
     */
    public $hosts = [];

    /**
     * @var CommitteeAdherentMandate|Collection
     */
    #[ORM\OneToMany(mappedBy: 'committee', targetEntity: CommitteeAdherentMandate::class, fetch: 'EXTRA_LAZY')]
    private $adherentMandates;

    #[AssertZoneType(types: Zone::COMMITTEE_TYPES, groups: ['api_committee_edition'])]
    #[Assert\Count(min: 1, minMessage: 'Le comité doit contenir au moins une zone.', groups: ['api_committee_edition'])]
    #[Groups(['committee:read', 'committee:write', 'admin_committee_update'])]
    #[ORM\ManyToMany(targetEntity: Zone::class, cascade: ['persist'])]
    protected Collection $zones;

    #[Assert\Expression('!this.animator or this.animator.isRenaissanceAdherent()', message: 'Président doit être un adhérent Renaissance.')]
    #[Groups(['committee:list', 'committee:read', 'committee:update_animator'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class, fetch: 'EAGER', inversedBy: 'animatorCommittees')]
    public ?Adherent $animator = null;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?UuidInterface $creator = null,
        ?string $name = null,
        ?string $description = null,
        ?AddressInterface $address = null,
        ?PhoneNumber $phone = null,
        ?string $slug = null,
        ?string $approvedAt = null,
        string $createdAt = 'now',
        int $membersCount = 0,
    ) {
        if ($approvedAt) {
            $approvedAt = new \DateTimeImmutable($approvedAt);
        }

        if ($createdAt) {
            $createdAt = new \DateTimeImmutable($createdAt);
        }

        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->createdBy = $creator;
        if ($name) {
            $this->setName($name);
        }
        $this->slug = $slug;
        $this->phone = $phone;
        $this->membersCount = $membersCount;
        $this->approvedAt = $approvedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
        $this->description = $description;
        $this->postAddress = $address;
        $this->adherentMandates = new ArrayCollection();
        $this->zones = new ZoneCollection();
        $this->committeeElections = new ArrayCollection();
    }

    #[Groups(['committee:read'])]
    public function getCommitteeElection(): ?CommitteeElection
    {
        return $this->getCurrentElection();
    }

    /**
     * @return ElectionEntityInterface[]
     */
    public function getElections(): array
    {
        return $this->committeeElections->toArray();
    }

    public function addElection(ElectionEntityInterface $election): void
    {
        if (!$this->committeeElections->contains($election)) {
            $election->setCommittee($this);
            $this->committeeElections->add($election);
        }
    }

    public static function createSimple(
        UuidInterface $uuid,
        string $creatorUuid,
        string $name,
        string $description,
        ?AddressInterface $address = null,
        ?PhoneNumber $phone = null,
        string $createdAt = 'now',
    ): self {
        $committee = new self(
            $uuid,
            Uuid::fromString($creatorUuid),
            $name,
            $description,
            $address,
            $phone
        );
        $committee->createdAt = new \DateTime($createdAt);

        return $committee;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getFacebookPageUrl(): ?string
    {
        return $this->facebookPageUrl;
    }

    public function getTwitterNickname(): ?string
    {
        return $this->twitterNickname;
    }

    public function isNameLocked(): bool
    {
        return $this->nameLocked;
    }

    public function setNameLocked(bool $nameLocked): void
    {
        $this->nameLocked = $nameLocked;
    }

    public function isBlocked(): bool
    {
        return \in_array($this->status, self::BLOCKED_STATUSES, true);
    }

    /**
     * Marks this committee as approved.
     */
    public function approved(string $timestamp = 'now'): void
    {
        $this->status = self::APPROVED;
        $this->approvedAt = new \DateTime($timestamp);
        $this->refusedAt = null;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setSocialNetworks(?string $facebookPageUrl = null, ?string $twitterNickname = null): void
    {
        $this->facebookPageUrl = $facebookPageUrl;
        $this->setTwitterNickname($twitterNickname);
    }

    public function setFacebookPageUrl($facebookPageUrl): void
    {
        $this->facebookPageUrl = $facebookPageUrl;
    }

    public function setTwitterNickname($twitterNickname): void
    {
        $this->twitterNickname = ltrim((string) $twitterNickname, '@');
    }

    public function update(string $name, string $description, AddressInterface $address): void
    {
        $this->setName($name);
        $this->description = $description;

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }
    }

    public function getReportType(): string
    {
        return ReportType::COMMITTEE;
    }

    public function getAdherentMandates(): Collection
    {
        return $this->adherentMandates;
    }

    public function addAdherentMandate(CommitteeAdherentMandate $adherentMandate): void
    {
        if (!$this->adherentMandates->contains($adherentMandate)) {
            $this->adherentMandates->add($adherentMandate);
        }
    }

    public function hasMaleAdherentMandate(): bool
    {
        return $this->hasAdherentMandateWithGender(Genders::MALE);
    }

    public function hasFemaleAdherentMandate(): bool
    {
        return $this->hasAdherentMandateWithGender(Genders::FEMALE);
    }

    public function hasAdherentMandateWithGender(string $gender): bool
    {
        if (0 === $this->adherentMandates->count()) {
            return false;
        }

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('finishAt', null))
            ->andWhere(Criteria::expr()->eq('quality', null))
            ->andWhere(Criteria::expr()->eq('gender', $gender))
        ;

        return $this->adherentMandates->matching($criteria)->count() > 0;
    }

    /**
     * @return CommitteeAdherentMandate[]|Collection
     */
    public function getActiveAdherentMandates(): Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('finishAt', null))
            ->andWhere(Criteria::expr()->eq('quality', null))
            ->orderBy(['gender' => 'ASC'])
        ;

        return $this->adherentMandates->matching($criteria);
    }

    /**
     * @return Adherent[]
     */
    public function getSupervisors(?bool $isProvisional = null): array
    {
        return array_map(function (CommitteeAdherentMandate $mandate) {
            return $mandate->getAdherent();
        }, $this->findSupervisorMandates(null, $isProvisional)->toArray());
    }

    /**
     * Marks this committee as closed.
     */
    public function close(): void
    {
        $this->status = self::CLOSED;
        $this->closedAt = new \DateTime();
    }

    public function isClosed(): bool
    {
        return self::CLOSED === $this->status;
    }

    /**
     * @return CommitteeAdherentMandate[]|Collection
     */
    public function findSupervisorMandates(?string $gender = null, ?bool $isProvisional = null): Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('quality', CommitteeMandateQualityEnum::SUPERVISOR))
            ->andWhere(Criteria::expr()->orX(
                Criteria::expr()->isNull('finishAt'),
                Criteria::expr()->gt('finishAt', new \DateTime())
            ))
        ;

        if ($gender) {
            $criteria->andWhere(Criteria::expr()->eq('gender', $gender));
        }

        if (\is_bool($isProvisional)) {
            $criteria->andWhere(Criteria::expr()->eq('provisional', $isProvisional));
        }

        return $this->adherentMandates->matching($criteria);
    }

    public function __toString()
    {
        return $this->name ?? '';
    }

    public static function createUuid(string $name): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, static::canonicalize($name));
    }

    public function setPhone(?PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function isApproved(): bool
    {
        return self::APPROVED === $this->status;
    }

    public function isPending(): bool
    {
        return self::PENDING === $this->status;
    }

    public function isRefused(): bool
    {
        return self::REFUSED === $this->status;
    }

    #[Groups(['committee:list'])]
    public function getMembersCount(): int
    {
        return $this->membersCount;
    }

    #[Groups(['committee:list'])]
    public function getAdherentsCount(): int
    {
        return $this->adherentsCount;
    }

    #[Groups(['committee:list'])]
    public function getSympathizersCount(): int
    {
        return $this->sympathizersCount;
    }

    #[Groups(['committee:list'])]
    public function getMembersEmCount(): int
    {
        return $this->membersEmCount;
    }

    public function updateMembersCount(
        bool $incrementAction,
        bool $isSympathizer,
        bool $isAdherent,
        bool $isActiveMembership,
    ): void {
        if ($incrementAction) {
            if ($isAdherent) {
                $this->incrementMembersCount();

                if ($isActiveMembership) {
                    $this->incrementAdherentsCount();
                }
            } elseif ($isSympathizer) {
                $this->incrementSympathizersCount();
            } else {
                $this->incrementMembersEmCount();
            }
        } else {
            if ($isAdherent) {
                $this->decrementMembersCount();

                if ($isActiveMembership) {
                    $this->decrementAdherentsCount();
                }
            } elseif ($isSympathizer) {
                $this->decrementSympathizersCount();
            } else {
                $this->decrementMembersEmCount();
            }
        }
    }

    public function incrementMembersCount(): void
    {
        ++$this->membersCount;
    }

    public function incrementAdherentsCount(): void
    {
        ++$this->adherentsCount;
    }

    public function incrementMembersEmCount(): void
    {
        ++$this->membersEmCount;
    }

    public function incrementSympathizersCount(): void
    {
        ++$this->sympathizersCount;
    }

    public function decrementMembersCount(): void
    {
        $this->membersCount = $this->membersCount < 1 ? 0 : $this->membersCount - 1;
    }

    public function decrementAdherentsCount(): void
    {
        $this->adherentsCount = $this->adherentsCount < 1 ? 0 : $this->adherentsCount - 1;
    }

    public function decrementSympathizersCount(): void
    {
        $this->sympathizersCount = $this->sympathizersCount < 1 ? 0 : $this->sympathizersCount - 1;
    }

    public function decrementMembersEmCount(): void
    {
        $this->membersEmCount = $this->membersEmCount < 1 ? 0 : $this->membersEmCount - 1;
    }

    /**
     * Marks this committee as refused/rejected.
     */
    public function refused(string $timestamp = 'now'): void
    {
        $this->status = self::REFUSED;
        $this->refusedAt = new \DateTime($timestamp);
        $this->approvedAt = null;
    }

    public function getRefusedAt(): ?\DateTimeInterface
    {
        return $this->refusedAt;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy?->toString();
    }

    public function isCreatedBy(UuidInterface $uuid): bool
    {
        return $this->createdBy && $this->createdBy->equals($uuid);
    }

    public function getApprovedAt(): ?\DateTimeInterface
    {
        return $this->approvedAt;
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }

    public function getUuidAsString(): string
    {
        return $this->getUuid()->toString();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function allowMembershipsMoving(): bool
    {
        $designation = $this->getCurrentDesignation();

        return !(
            $designation
            && $designation->getElectionCreationDate()
            && $designation->getElectionCreationDate() < ($now = new \DateTime())
            && $designation->getVoteEndDate() > $now
        );
    }

    public function countElections(): int
    {
        return $this->committeeElections->count();
    }
}
