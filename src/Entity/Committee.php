<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Address\AddressInterface;
use App\AdherentMessage\StaticSegmentInterface;
use App\Api\Filter\InZoneOfScopeFilter;
use App\Committee\Exception\CommitteeProvisionalSupervisorException;
use App\Committee\Exception\CommitteeSupervisorException;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\Geo\Zone;
use App\Entity\Report\ReportableInterface;
use App\Entity\VotingPlatform\Designation\ElectionEntityInterface;
use App\Entity\VotingPlatform\Designation\EntityElectionHelperTrait;
use App\Exception\CommitteeAlreadyApprovedException;
use App\Geocoder\GeoPointInterface;
use App\Report\ReportType;
use App\Validator\ZoneType as AssertZoneType;
use App\ValueObject\Genders;
use App\ValueObject\Link;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This entity represents a committee group.
 *
 * @ApiResource(
 *     routePrefix="/v3",
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"committee:list"},
 *         },
 *         "validation_groups": {"api_committee_edition"},
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'committee')",
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/committees/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'committee') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object)",
 *             "normalization_context": {
 *                 "groups": {"committee:list", "committee:read"},
 *             },
 *         },
 *         "put": {
 *             "path": "/committees/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'committee') and is_granted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', object)",
 *             "denormalization_context": {
 *                 "groups": {"committee:write"},
 *             },
 *         },
 *     },
 *     collectionOperations={
 *         "get",
 *         "post": {
 *             "denormalization_context": {
 *                 "groups": {"committee:write"},
 *             },
 *         },
 *     }
 * )
 *
 * @ApiFilter(InZoneOfScopeFilter::class)
 *
 * @ORM\Table(
 *     name="committees",
 *     indexes={
 *         @ORM\Index(columns={"status"}),
 *         @ORM\Index(columns={"version"}),
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CommitteeRepository")
 */
class Committee implements SynchronizedEntity, ReferentTaggableEntity, StaticSegmentInterface, AddressHolderInterface, ZoneableEntity, ExposedObjectInterface, EntityAdherentBlameableInterface, GeoPointInterface, CoordinatorAreaInterface, ReportableInterface, EntityAdministratorBlameableInterface
{
    use EntityNullablePostAddressTrait;
    use EntityReferentTagTrait;
    use EntityZoneTrait;
    use EntityElectionHelperTrait;
    use StaticSegmentTrait;
    use EntityAdherentBlameableTrait;
    use EntityAdministratorBlameableTrait;
    use CoordinatorAreaTrait;
    use EntityIdentityTrait;
    use EntityCrudTrait;
    use EntityTimestampableTrait;
    use EntityNameSlugTrait;

    public const APPROVED = 'APPROVED';
    public const PENDING = 'PENDING';
    public const REFUSED = 'REFUSED';
    public const CLOSED = 'CLOSED';

    public const WAITING_STATUSES = [
        self::PENDING,
        self::PRE_APPROVED,
        self::PRE_REFUSED,
    ];

    public const BLOCKED_STATUSES = [
        self::CLOSED,
        self::REFUSED,
    ];

    /**
     * The group current status.
     *
     * @ORM\Column(length=20)
     */
    protected $status;

    /**
     * The timestamp when an administrator approved this group.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $approvedAt;

    /**
     * The timestamp when an administrator refused this group.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $refusedAt;

    /**
     * The adherent UUID who created this group.
     *
     * @ORM\Column(type="uuid", nullable=true)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closedAt;

    /**
     * @ORM\Column(type="phone_number", nullable=true)
     */
    private $phone;

    /**
     * The cached number of members (followers and hosts/administrators).
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     *
     * @Groups({"committee_sync"})
     */
    private $membersCount;

    /**
     * The group description.
     *
     * @ORM\Column(type="text")
     *
     * @Groups({"committee:list", "committee:write"})
     */
    private $description;

    /**
     * The committee Facebook page URL.
     *
     * @ORM\Column(nullable=true)
     */
    private $facebookPageUrl;

    /**
     * The committee Twitter nickname.
     *
     * @ORM\Column(nullable=true)
     */
    private $twitterNickname;

    /**
     * Is also used to block address modification.
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $nameLocked = false;

    /**
     * @var CommitteeElection[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CommitteeElection", mappedBy="committee", cascade={"all"}, orphanRemoval=true)
     */
    private $committeeElections;

    /**
     * @var ProvisionalSupervisor[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ProvisionalSupervisor", mappedBy="committee", cascade={"all"}, orphanRemoval=true)
     */
    private $provisionalSupervisors;

    /**
     * A cached list of the hosts (for admin).
     */
    public $hosts = [];

    /**
     * @var CommitteeAdherentMandate|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\AdherentMandate\CommitteeAdherentMandate", mappedBy="committee", fetch="EXTRA_LAZY")
     */
    private $adherentMandates;

    /**
     * @ORM\Column(type="smallint", options={"unsigned": true, "default": "2"})
     */
    public int $version = 2;

    /**
     * @var Collection|Zone[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone", cascade={"persist"})
     *
     * @Groups({
     *     "committee:read",
     *     "committee:write",
     * })
     *
     * @Assert\Count(min=1, minMessage="Le comité doit contenir au moins une zone.", groups={"api_committee_edition"})
     * @AssertZoneType(types=Zone::COMMITTEE_TYPES, groups={"api_committee_edition"})
     */
    protected $zones;

    public function __construct(
        UuidInterface $uuid = null,
        UuidInterface $creator = null,
        string $name = null,
        string $description = null,
        AddressInterface $address = null,
        PhoneNumber $phone = null,
        string $slug = null,
        string $status = self::PENDING,
        string $approvedAt = null,
        string $createdAt = 'now',
        int $membersCount = 0,
        array $referentTags = []
    ) {
        if ($approvedAt) {
            $approvedAt = new \DateTimeImmutable($approvedAt);
        }

        if ($createdAt) {
            $createdAt = new \DateTimeImmutable($createdAt);
        }

        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->createdBy = $creator;
        $this->setName($name);
        $this->slug = $slug;
        $this->phone = $phone;
        $this->status = $status;
        $this->membersCount = $membersCount;
        $this->approvedAt = $approvedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
        $this->description = $description;
        $this->postAddress = $address;
        $this->adherentMandates = new ArrayCollection();
        $this->referentTags = new ArrayCollection();
        $this->zones = new ArrayCollection();
        $this->committeeElections = new ArrayCollection();
        $this->provisionalSupervisors = new ArrayCollection();

        foreach ($referentTags as $referentTag) {
            $this->addReferentTag($referentTag);
        }
    }

    public function getPostAddress(): AddressInterface
    {
        return $this->postAddress;
    }

    /**
     * @Groups({"committee:read"})
     */
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
        AddressInterface $address = null,
        PhoneNumber $phone = null,
        string $createdAt = 'now'
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

    public static function createForAdherent(
        Adherent $adherent,
        string $name,
        string $description,
        AddressInterface $address,
        ?PhoneNumber $phone = null,
        string $createdAt = 'now'
    ): self {
        $committee = new self(
            self::createUuid($name),
            clone $adherent->getUuid(),
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

    public function isWaitingForApproval(): bool
    {
        return \in_array($this->status, self::WAITING_STATUSES, true) && !$this->approvedAt;
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
        if ($this->isApproved()) {
            throw new CommitteeAlreadyApprovedException($this->uuid);
        }

        $this->status = self::APPROVED;
        $this->approvedAt = new \DateTime($timestamp);
        $this->refusedAt = null;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setSocialNetworks(string $facebookPageUrl = null, string $twitterNickname = null)
    {
        $this->facebookPageUrl = $facebookPageUrl;
        $this->setTwitterNickname($twitterNickname);
    }

    public function setFacebookPageUrl($facebookPageUrl)
    {
        $this->facebookPageUrl = $facebookPageUrl;
    }

    public function setTwitterNickname($twitterNickname)
    {
        $this->twitterNickname = ltrim((string) $twitterNickname, '@');
    }

    /**
     * Returns the list of social networks links.
     *
     * @return Link[]
     */
    public function getSocialNetworksLinks(): array
    {
        $links = [];

        if ($this->facebookPageUrl) {
            $links['facebook'] = $this->createLink($this->facebookPageUrl, 'Facebook');
        }

        if ($this->twitterNickname) {
            $links['twitter'] = $this->createLink(sprintf('https://twitter.com/%s', $this->twitterNickname), 'Twitter');
        }

        return $links;
    }

    public function update(string $name, string $description, AddressInterface $address): void
    {
        $this->setName($name);
        $this->description = $description;

        if (!$this->postAddress->equals($address)) {
            $this->postAddress = $address;
        }
    }

    private function createLink(string $url, string $label): Link
    {
        return new Link($url, $label);
    }

    public function getReportType(): string
    {
        return ReportType::COMMITTEE;
    }

    public function getAdherentMandates(): Collection
    {
        return $this->adherentMandates;
    }

    public function setAdherentMandates(Collection $adherentMandates): void
    {
        $this->adherentMandates = $adherentMandates;
    }

    public function addAdherentMandate(CommitteeAdherentMandate $adherentMandate): void
    {
        if (!$this->adherentMandates->contains($adherentMandate)) {
            $this->adherentMandates->add($adherentMandate);
        }
    }

    public function removeAdherentMandate(CommitteeAdherentMandate $adherentMandate): void
    {
        $this->adherentMandates->removeElement($adherentMandate);
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

    public function getActiveAdherentMandateAdherentIds(): array
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('finishAt', null))
            ->andWhere(Criteria::expr()->eq('quality', null))
            ->orderBy(['gender' => 'ASC'])
        ;

        return $this->adherentMandates
            ->matching($criteria)
            ->map(function (CommitteeAdherentMandate $adherentMandate) {
                return $adherentMandate->getAdherent()->getId();
            })
            ->toArray()
        ;
    }

    /**
     * @return ProvisionalSupervisor[]|Collection
     */
    public function getProvisionalSupervisors(): Collection
    {
        return $this->provisionalSupervisors;
    }

    public function hasProvisionalSupervisor(Adherent $adherent): bool
    {
        $found = $this->provisionalSupervisors->filter(function (ProvisionalSupervisor $ps) use ($adherent) {
            return $ps->getAdherent() === $adherent;
        });

        return $found->count() > 0;
    }

    public function getProvisionalSupervisorByGender(string $gender): ?ProvisionalSupervisor
    {
        $found = $this->provisionalSupervisors->filter(function (ProvisionalSupervisor $ps) use ($gender) {
            return $ps->getAdherent()->getGender() === $gender;
        });

        $count = $found->count();

        if ($count > 1) {
            throw new CommitteeProvisionalSupervisorException(sprintf('More than one %s provisional supervisor has been found for committee with UUID "%s".', $this->getUuid(), $gender));
        }

        return $count > 0 ? $found->first() : null;
    }

    public function addProvisionalSupervisor(Adherent $adherent): void
    {
        if (!$this->hasProvisionalSupervisor($adherent)) {
            $provisionalSupervisor = new ProvisionalSupervisor($adherent, $this);
            $this->provisionalSupervisors->add($provisionalSupervisor);
        }
    }

    public function removeProvisionalSupervisor(ProvisionalSupervisor $provisionalSupervisor): void
    {
        $this->provisionalSupervisors->removeElement($provisionalSupervisor);
    }

    public function updateProvisionalSupervisor(Adherent $adherent): void
    {
        if ($ps = $this->getProvisionalSupervisorByGender($adherent->getGender())) {
            $this->removeProvisionalSupervisor($ps);
        }

        $this->addProvisionalSupervisor($adherent);
    }

    public function getSupervisorMandate(string $gender, bool $isProvisional = false): ?CommitteeAdherentMandate
    {
        $mandates = $this->findSupervisorMandates($gender, $isProvisional);
        $count = $mandates->count();

        if ($count > 1) {
            throw new CommitteeSupervisorException(sprintf('More than one %s %s supervisor has been found for committee with UUID "%s".', $gender, $isProvisional ? 'provisional' : '', $this->getUuid()));
        }

        return $count > 0 ? $mandates->first() : null;
    }

    /**
     * @return Adherent[]
     */
    public function getSupervisors(bool $isProvisional = null): array
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
    public function findSupervisorMandates(?string $gender = null, bool $isProvisional = null): Collection
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

    public function getNormalizationGroups(): array
    {
        return [
            'event_read',
        ];
    }

    public function getExposedRouteName(): string
    {
        return 'app_committee_show';
    }

    public function getExposedRouteParams(): array
    {
        return ['slug' => $this->getSlug()];
    }

    public function __toString()
    {
        return $this->name ?? '';
    }

    public static function createUuid(string $name): UuidInterface
    {
        return Uuid::uuid5(Uuid::NAMESPACE_OID, static::canonicalize($name));
    }

    public function setPhone(PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function isApproved(): bool
    {
        return self::APPROVED === $this->status && $this->approvedAt;
    }

    public function isPending(): bool
    {
        return self::PENDING === $this->status;
    }

    public function isRefused(): bool
    {
        return self::REFUSED === $this->status;
    }

    public function getMembersCount(): int
    {
        return $this->membersCount;
    }

    public function incrementMembersCount(int $increment = 1): void
    {
        $this->membersCount += $increment;
    }

    public function decrementMembersCount(int $increment = 1): void
    {
        $this->membersCount = $increment >= $this->membersCount ? 0 : $this->membersCount - $increment;
    }

    /**
     * Marks this committee as refused/rejected.
     */
    public function refused(string $timestamp = 'now')
    {
        $this->status = self::REFUSED;
        $this->refusedAt = new \DateTime($timestamp);
        $this->approvedAt = null;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy ? $this->createdBy->toString() : null;
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

    public function getVersion(): int
    {
        return $this->version;
    }

    public function isVersion2(): bool
    {
        return 2 === $this->version;
    }
}
