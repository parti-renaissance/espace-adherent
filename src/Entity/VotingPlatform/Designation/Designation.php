<?php

namespace App\Entity\VotingPlatform\Designation;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Api\Filter\InZoneOfScopeFilter;
use App\Api\Validator\UpdateDesignationGroupGenerator;
use App\Collection\ZoneCollection;
use App\Controller\Api\VotingPlatform\CancelElectionController;
use App\Entity\CmsBlock;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\InjectScopeZonesInterface;
use App\Entity\VotingPlatform\Designation\CandidacyPool\CandidacyPool;
use App\Entity\VotingPlatform\Designation\Poll\Poll;
use App\Entity\VotingPlatform\Designation\Poll\PollQuestion;
use App\Entity\VotingPlatform\Designation\Poll\QuestionChoice;
use App\Entity\VotingPlatform\ElectionPoolCodeEnum;
use App\Entity\ZoneableEntityInterface;
use App\JeMengage\Alert\AlertOwnerInterface;
use App\Repository\VotingPlatform\DesignationRepository;
use App\VotingPlatform\Designation\CreatePartialDesignationCommand;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: InZoneOfScopeFilter::class)]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['type' => 'exact'])]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/designations/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'designation')"
        ),
        new Put(
            uriTemplate: '/designations/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'designation')",
            validationContext: ['groups' => UpdateDesignationGroupGenerator::class]
        ),
        new HttpOperation(
            method: 'PUT',
            uriTemplate: '/designations/{uuid}/cancel',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: CancelElectionController::class,
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'designation')",
            deserialize: false
        ),
        new GetCollection(normalizationContext: ['groups' => ['designation_list']]),
        new Post(validationContext: ['groups' => ['api_designation_write']]),
    ],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['designation_read']],
    denormalizationContext: ['groups' => ['designation_write']],
    order: ['voteStartDate' => 'DESC'],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'designation')"
)]
#[ORM\Entity(repositoryClass: DesignationRepository::class)]
class Designation implements EntityAdministratorBlameableInterface, EntityAdherentBlameableInterface, ZoneableEntityInterface, InjectScopeZonesInterface, AlertOwnerInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityZoneTrait;
    use EntityAdministratorBlameableTrait;
    use EntityAdherentBlameableTrait;

    public const DENOMINATION_DESIGNATION = 'désignation';
    public const DENOMINATION_ELECTION = 'élection';

    public const NOTIFICATION_ALL = [
        'Ouverture du vote' => self::NOTIFICATION_VOTE_OPENED,
        'Fermeture du vote' => self::NOTIFICATION_VOTE_CLOSED,
        'Résultats disponible' => self::NOTIFICATION_RESULT_READY,
        'Rappel de vote J-1' => self::NOTIFICATION_VOTE_REMINDER_1D,
        'Rappel de vote H-1' => self::NOTIFICATION_VOTE_REMINDER_1H,
        'Ouverture du tour bis' => self::NOTIFICATION_SECOND_ROUND,
        'Annonce du vote J-2' => self::NOTIFICATION_VOTE_ANNOUNCEMENT,
    ];

    public const NOTIFICATION_VOTE_OPENED = 1;
    public const NOTIFICATION_VOTE_CLOSED = 2;
    public const NOTIFICATION_VOTE_REMINDER_1D = 4;
    public const NOTIFICATION_SECOND_ROUND = 8;
    public const NOTIFICATION_RESULT_READY = 16;
    public const NOTIFICATION_VOTE_REMINDER_1H = 32;
    public const NOTIFICATION_VOTE_ANNOUNCEMENT = 64;

    /**
     * @var string|null
     */
    #[Assert\NotBlank(groups: ['Admin'])]
    #[ORM\Column(nullable: true)]
    private $label;

    #[Assert\Length(max: 255, groups: ['api_designation_write', 'api_designation_write_limited'])]
    #[Assert\NotBlank(groups: ['api_designation_write', 'api_designation_write_limited'])]
    #[Groups(['designation_read', 'designation_write', 'designation_list', 'designation_write_limited', 'committee_election:read'])]
    #[ORM\Column(nullable: true)]
    public ?string $customTitle = null;

    /**
     * @var string|null
     */
    #[Assert\Choice(choices: DesignationTypeEnum::MAIN_TYPES, groups: ['Default'])]
    #[Assert\Choice(choices: DesignationTypeEnum::API_AVAILABLE_TYPES, groups: ['api_designation_write'])]
    #[Assert\NotBlank(groups: ['Default', 'api_designation_write'])]
    #[Groups(['designation_read', 'designation_write', 'designation_list'])]
    #[ORM\Column]
    private $type;

    #[Assert\Expression('!(this.isConsultationType() or this.isVoteType()) or value', groups: ['api_designation_write', 'api_designation_write_limited'])]
    #[Groups(['designation_read', 'designation_write'])]
    #[ORM\Column(type: 'smallint', nullable: true)]
    public ?int $targetYear = null;

    /**
     * @var string[]|null
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $globalZones;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $candidacyStartDate;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $candidacyEndDate;

    #[Groups(['designation_read', 'committee_election:read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $electionCreationDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $accountCreationDeadline = null;

    /**
     * @var \DateTime|null
     */
    #[Assert\GreaterThan('now', message: 'La date de début doit être dans le futur.', groups: ['Admin_creation', 'api_designation_write'])]
    #[Groups(['designation_read', 'designation_write', 'designation_list', 'committee_election:read'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $voteStartDate;

    /**
     * @var \DateTime|null
     */
    #[Assert\Expression('value > this.getVoteStartDate()', message: 'La date de clôture doit être postérieur à la date de début', groups: ['Default', 'api_designation_write'])]
    #[Groups(['designation_read', 'designation_write', 'committee_election:read', 'designation_list'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $voteEndDate;

    /**
     * @var int
     */
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    private $resultDisplayDelay = 30;

    /**
     * Display the election results after this delay (in hours)
     *
     * @var float
     */
    #[Assert\GreaterThanOrEqual(0)]
    #[ORM\Column(type: 'float', options: ['unsigned' => true, 'default' => 0])]
    private $resultScheduleDelay = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $resultDisplayBlank = false;

    /**
     * Duration of the additional round in day
     *
     * @var int
     */
    #[Assert\GreaterThan(0)]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    private $additionalRoundDuration = 5;

    /**
     * @var int
     */
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true])]
    private $lockPeriodThreshold = 3;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $limited = false;

    /**
     * @var string
     */
    #[ORM\Column(options: ['default' => self::DENOMINATION_ELECTION])]
    private $denomination = self::DENOMINATION_ELECTION;

    /**
     * @var array|null
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $pools;

    #[Assert\Length(max: 2000, groups: ['api_designation_write', 'api_designation_write_limited'])]
    #[Assert\NotBlank(groups: ['api_designation_write', 'api_designation_write_limited'])]
    #[Groups(['designation_read', 'designation_write', 'designation_write_limited'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $notifications =
        self::NOTIFICATION_VOTE_OPENED +
        self::NOTIFICATION_RESULT_READY +
        self::NOTIFICATION_VOTE_REMINDER_1D +
        self::NOTIFICATION_VOTE_REMINDER_1H +
        self::NOTIFICATION_VOTE_ANNOUNCEMENT;

    #[ORM\Column(type: 'boolean')]
    private bool $isBlankVoteEnabled = true;

    #[Assert\Expression('!(this.isLocalPollType() or this.isConsultationType() or this.isVoteType()) or value', message: 'Vous devez préciser le questionnaire qui sera utilisé pour cette élection.', groups: ['Admin_creation', 'api_designation_write'])]
    #[Assert\Valid(groups: ['api_designation_write'])]
    #[ORM\ManyToOne(targetEntity: Poll::class, cascade: ['persist'])]
    public ?Poll $poll = null;

    /**
     * @var CandidacyPool[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'designation', targetEntity: CandidacyPool::class, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private $candidacyPools;

    #[ORM\ManyToOne(targetEntity: CmsBlock::class)]
    public ?CmsBlock $wordingWelcomePage = null;

    #[ORM\ManyToOne(targetEntity: CmsBlock::class)]
    public ?CmsBlock $wordingRegulationPage = null;

    #[Assert\Expression('!this.isPartyListProportionalType() or value', message: 'Vous devez préciser le nombre des sièges à distribuer.')]
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    public ?int $seats = null;

    #[Assert\GreaterThan(0)]
    #[Assert\LessThanOrEqual(100)]
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    public ?int $majorityPrime = null;

    #[Assert\Expression('!this.isPartyListProportionalType() or !this.majorityPrime or null != value', message: "Vous devez préciser le mode d'arrondi pour la prime majoritaire.")]
    #[ORM\Column(type: 'boolean', nullable: true)]
    public ?bool $majorityPrimeRoundSupMode = null;

    #[Assert\Expression('!this.isCommitteeSupervisorType() or value', message: 'Un identifiant est requis pour ce champs.', groups: ['api_designation_write'])]
    #[Groups(['designation_read', 'designation_write'])]
    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?UuidInterface $electionEntityIdentifier = null;

    #[Groups(['designation_read', 'designation_list'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $isCanceled = false;

    #[ORM\Column(nullable: true)]
    public ?string $alertTitle = null;

    #[ORM\Column(nullable: true)]
    public ?string $alertCtaLabel = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $alertDescription = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $alertBeginAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $enableVoteQuestionsPreview = true;

    public function __construct(?string $label = null, ?UuidInterface $uuid = null)
    {
        $this->label = $label;
        $this->uuid = $uuid ?? Uuid::uuid4();

        $this->zones = new ZoneCollection();
        $this->candidacyPools = new ArrayCollection();
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;

        if ($this->isPollType()) {
            $this->limited = true;
        }

        if (!$this->isDenominationEditable()) {
            $this->denomination = self::DENOMINATION_ELECTION;
        }
    }

    public function getGlobalZones(): ?array
    {
        return $this->globalZones;
    }

    public function setGlobalZones(?array $globalZones): void
    {
        $this->globalZones = $globalZones;
    }

    public function getCandidacyStartDate(): ?\DateTime
    {
        return $this->candidacyStartDate;
    }

    public function setCandidacyStartDate(?\DateTime $candidacyStartDate): void
    {
        $this->candidacyStartDate = $candidacyStartDate;
    }

    public function getCandidacyEndDate(): ?\DateTime
    {
        return $this->candidacyEndDate;
    }

    public function setCandidacyEndDate(?\DateTime $candidacyEndDate): void
    {
        $this->candidacyEndDate = $candidacyEndDate;
    }

    public function getVoteStartDate(): ?\DateTime
    {
        return $this->voteStartDate;
    }

    public function setVoteStartDate(?\DateTime $voteStartDate): void
    {
        $this->voteStartDate = $voteStartDate;
    }

    public function getVoteEndDate(): ?\DateTime
    {
        return $this->voteEndDate;
    }

    public function setVoteEndDate(?\DateTime $voteEndDate): void
    {
        $this->voteEndDate = $voteEndDate;
    }

    public function getResultStartDate(?\DateTime $voteEndDate = null): \DateTime
    {
        $date = $voteEndDate ?? $this->voteEndDate;

        if (!$this->resultScheduleDelay || !$date) {
            return $date;
        }

        return (clone $date)->modify(\sprintf('+%d minutes', \intval($this->resultScheduleDelay * 60)));
    }

    public function getResultDisplayDelay(): int
    {
        return $this->resultDisplayDelay;
    }

    public function setResultDisplayDelay(int $resultDisplayDelay): void
    {
        $this->resultDisplayDelay = $resultDisplayDelay;
    }

    public function getResultScheduleDelay(): float
    {
        return $this->resultScheduleDelay;
    }

    public function setResultScheduleDelay(float $resultScheduleDelay): void
    {
        $this->resultScheduleDelay = $resultScheduleDelay;
    }

    public function getAdditionalRoundDuration(): int
    {
        return $this->additionalRoundDuration;
    }

    public function setAdditionalRoundDuration(int $additionalRoundDuration): void
    {
        $this->additionalRoundDuration = $additionalRoundDuration;
    }

    public function getDetailedType(): string
    {
        if ($this->pools) {
            $suffix = null;
            if (\in_array(ElectionPoolCodeEnum::FEMALE, $this->pools, true)) {
                $suffix = ElectionPoolCodeEnum::FEMALE;
            } elseif (\in_array(ElectionPoolCodeEnum::MALE, $this->pools, true)) {
                $suffix = ElectionPoolCodeEnum::MALE;
            }

            if ($suffix) {
                return \sprintf('%s_%s', $this->type, strtolower($suffix));
            }
        }

        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->customTitle ?? DesignationTypeEnum::TITLES[$this->getDetailedType()] ?? '';
    }

    public function getLockPeriodThreshold(): int
    {
        return $this->lockPeriodThreshold;
    }

    public function setLockPeriodThreshold(int $lockPeriodThreshold): void
    {
        $this->lockPeriodThreshold = $lockPeriodThreshold;
    }

    public function getNotifications(): int
    {
        return $this->notifications ?? array_sum(self::NOTIFICATION_ALL);
    }

    public function setNotifications(int $notifications): void
    {
        $this->notifications = $notifications;
    }

    public function isNotificationEnabled(string $notificationBit): bool
    {
        return 0 !== ($this->notifications & $notificationBit);
    }

    #[Assert\IsTrue(message: 'La combinaison des dates est invalide.')]
    public function hasValidDates(): bool
    {
        if ($this->isCandidacyPeriodEnabled()) {
            $result =
                !empty($this->candidacyStartDate)
                && (
                    (!empty($this->candidacyEndDate) && !empty($this->voteStartDate) && !empty($this->voteEndDate))
                    || (empty($this->candidacyEndDate) && empty($this->voteStartDate) && empty($this->voteEndDate))
                );
        } else {
            $result = !empty($this->voteStartDate)
                && !empty($this->voteEndDate)
                && $this->voteStartDate < $this->voteEndDate;
        }

        if (!$result) {
            return false;
        }

        if ($this->electionCreationDate) {
            return $this->voteStartDate && $this->electionCreationDate < $this->voteStartDate;
        }

        return true;
    }

    #[Assert\IsTrue(message: 'La configuration de la zone est invalide', groups: ['Default', 'Admin'])]
    public function hasValidZone(): bool
    {
        if (\in_array($this->type, [
            DesignationTypeEnum::EXECUTIVE_OFFICE,
            DesignationTypeEnum::POLL,
            DesignationTypeEnum::LOCAL_POLL,
            DesignationTypeEnum::CONSULTATION,
            DesignationTypeEnum::VOTE,
            DesignationTypeEnum::TERRITORIAL_ASSEMBLY,
            DesignationTypeEnum::CONGRESS_CN,
        ], true)) {
            return true;
        }

        // no need to have a zone for committee partial elections
        if ($this->isCommitteeTypes() && $this->isLimited()) {
            return true;
        }

        return
            ($this->isCommitteeTypes() && !empty($this->globalZones))
            || ($this->isLocalElectionType() && !$this->zones->isEmpty());
    }

    public function isOngoing(): bool
    {
        $now = new \DateTime();

        return $this->getCandidacyStartDate() <= $now
            && (null === $this->getVoteEndDate() || $now < $this->getVoteEndDate());
    }

    public function isActive(): bool
    {
        $now = new \DateTime();

        return $this->getCandidacyStartDate() <= $now
            && (
                null === $this->getVoteEndDate()
                || $now < $this->getVoteEndDate()
                || $this->isResultPeriodActive()
            );
    }

    public function getElectionCreationDate(): ?\DateTime
    {
        return $this->electionCreationDate;
    }

    public function isResultPeriodActive(): bool
    {
        $now = new \DateTime();

        return $this->getVoteEndDate()
            && $this->getVoteEndDate() <= $now
            && $now < $this->getResultEndDate();
    }

    public function getResultEndDate(): ?\DateTime
    {
        if (!$this->getVoteEndDate()) {
            return null;
        }

        return (clone $this->getVoteEndDate())->modify(\sprintf('+%d days', $this->getResultDisplayDelay()));
    }

    public function markAsLimited(): void
    {
        $this->limited = true;
    }

    public function __clone()
    {
        $this->id = null;
        $this->uuid = Uuid::uuid4();
    }

    public function isCommitteeTypes(): bool
    {
        return \in_array($this->type, [DesignationTypeEnum::COMMITTEE_ADHERENT, DesignationTypeEnum::COMMITTEE_SUPERVISOR], true);
    }

    public function isCommitteeAdherentType(): bool
    {
        return DesignationTypeEnum::COMMITTEE_ADHERENT === $this->type;
    }

    public function isCommitteeSupervisorType(): bool
    {
        return DesignationTypeEnum::COMMITTEE_SUPERVISOR === $this->type;
    }

    public function isBinomeDesignation(): bool
    {
        return \in_array($this->type, [
            DesignationTypeEnum::COMMITTEE_SUPERVISOR,
            DesignationTypeEnum::COPOL,
        ], true);
    }

    public function isPollType(): bool
    {
        return DesignationTypeEnum::POLL === $this->type;
    }

    public function isLocalElectionType(): bool
    {
        return DesignationTypeEnum::LOCAL_ELECTION === $this->type;
    }

    public function isLocalElectionTypes(): bool
    {
        return \in_array($this->type, [
            DesignationTypeEnum::LOCAL_POLL,
            DesignationTypeEnum::LOCAL_ELECTION,
            DesignationTypeEnum::CONSULTATION,
            DesignationTypeEnum::VOTE,
        ]);
    }

    public function isLocalPollType(): bool
    {
        return DesignationTypeEnum::LOCAL_POLL === $this->type;
    }

    public function isConsultationType(): bool
    {
        return DesignationTypeEnum::CONSULTATION === $this->type;
    }

    public function isVoteType(): bool
    {
        return DesignationTypeEnum::VOTE === $this->type;
    }

    public function isTerritorialAssemblyType(): bool
    {
        return DesignationTypeEnum::TERRITORIAL_ASSEMBLY === $this->type;
    }

    public function isCongressCNType(): bool
    {
        return DesignationTypeEnum::CONGRESS_CN === $this->type;
    }

    public function isExecutiveOfficeType(): bool
    {
        return DesignationTypeEnum::EXECUTIVE_OFFICE === $this->type;
    }

    public function isMajorityType(): bool
    {
        return DesignationTypeEnum::NATIONAL_COUNCIL === $this->type;
    }

    public function isPartyListProportionalType(): bool
    {
        return $this->isLocalElectionType() || $this->isCongressCNType();
    }

    public function getDenomination(bool $withDeterminer = false, bool $ucfirst = false): string
    {
        if ($withDeterminer) {
            if (self::DENOMINATION_ELECTION === $this->denomination) {
                return ($ucfirst ? 'L\'' : 'l\'').$this->denomination;
            }

            return ($ucfirst ? 'La ' : 'la ').$this->denomination;
        }

        return $ucfirst ?
            mb_strtoupper(mb_substr($this->denomination, 0, 1)).mb_substr($this->denomination, 1)
            : $this->denomination;
    }

    public function setDenomination(string $denomination): void
    {
        if (!$this->isDenominationEditable()) {
            return;
        }

        $this->denomination = $denomination;
    }

    public function isDenominationEditable(): bool
    {
        return
            !$this->isLocalElectionTypes()
            && !$this->isCommitteeSupervisorType()
            && !$this->isConsultationType()
            && !$this->isVoteType()
            && !$this->isTerritorialAssemblyType()
            && !$this->isCongressCNType();
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }

    public function getPools(): ?array
    {
        return $this->pools;
    }

    public function setPools(?array $pools): void
    {
        $this->pools = $pools;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isLimited(): bool
    {
        return $this->limited;
    }

    public static function createPartialFromCommand(CreatePartialDesignationCommand $command): self
    {
        $designation = new self();

        $designation->setType($command->getDesignationType());

        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $designation->getType()) {
            $designation->setDenomination(Designation::DENOMINATION_ELECTION);
        }

        if ($command->getPool()) {
            $designation->setPools([$command->getPool()]);
        }

        $designation->setCandidacyStartDate(new \DateTime());
        $designation->setCandidacyEndDate((clone $command->getVoteStartDate())->modify('-24 hours'));

        $designation->setVoteStartDate($command->getVoteStartDate());
        $designation->setVoteEndDate($command->getVoteEndDate());

        $designation->setLabel('[Partielle] '.$command->getCommittee()->getName());
        $designation->setDescription($command->getMessage());

        $designation->markAsLimited();

        return $designation;
    }

    public function isBlankVoteAvailable(): bool
    {
        return !$this->isExecutiveOfficeType();
    }

    public function isBlankVoteEnabled(): bool
    {
        return $this->isBlankVoteAvailable() && $this->isBlankVoteEnabled;
    }

    public function isResultBlankVoteEnabled(): bool
    {
        return $this->isBlankVoteEnabled() && $this->resultDisplayBlank;
    }

    public function setIsBlankVoteEnabled(bool $value): void
    {
        $this->isBlankVoteEnabled = $value;
    }

    public function isSecondRoundEnabled(): bool
    {
        return \in_array($this->type, [
            DesignationTypeEnum::COMMITTEE_ADHERENT,
            DesignationTypeEnum::COPOL,
            DesignationTypeEnum::NATIONAL_COUNCIL,
        ]);
    }

    public function isVotePeriodStarted(): bool
    {
        return $this->getVoteStartDate() && $this->getVoteStartDate() <= (new \DateTime());
    }

    public function isCandidacyPeriodEnabled(): bool
    {
        return
            !$this->isLocalElectionTypes()
            && !$this->isCongressCNType();
    }

    public function getElectionEntityIdentifier(): ?UuidInterface
    {
        return $this->electionEntityIdentifier;
    }

    public function setElectionEntityIdentifier(?UuidInterface $electionEntityIdentifier): void
    {
        $this->electionEntityIdentifier = $electionEntityIdentifier;
    }

    public function isCanceled(): bool
    {
        return $this->isCanceled;
    }

    public function cancel(): void
    {
        $this->isCanceled = true;
    }

    /** @return CandidacyPool[] */
    public function getCandidacyPools(): array
    {
        return $this->candidacyPools->toArray();
    }

    public function addCandidacyPool(CandidacyPool $candidacyPool): void
    {
        if (!$this->candidacyPools->contains($candidacyPool)) {
            $candidacyPool->designation = $this;
            $this->candidacyPools->add($candidacyPool);
        }
    }

    public function removeCandidacyPool(CandidacyPool $candidacyPool): void
    {
        $this->candidacyPools->remove($candidacyPool);
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    #[Groups(['designation_read'])]
    public function isFullyEditable(): bool
    {
        return !$this->isCanceled() && $this->getVoteStartDate() > (new \DateTime('+2 days'));
    }

    #[Groups(['designation_read'])]
    public function getQuestions(): array
    {
        return $this->poll ? $this->poll->getQuestions() : [];
    }

    #[Groups(['designation_write'])]
    public function setQuestions(array $questions): void
    {
        if (!$questions) {
            return;
        }

        if (!$this->poll) {
            $this->poll = new Poll('[API] '.$this->getTitle());
        }

        $this->poll->clearQuestions();

        foreach ($questions as $question) {
            if (empty($question['choices'])) {
                continue;
            }

            $pollQuestion = new PollQuestion($question['content'] ?? null);

            foreach ($question['choices'] as $choice) {
                $pollQuestion->addChoice(new QuestionChoice($choice['label'] ?? null));
            }

            $this->poll->addQuestion($pollQuestion);
        }
    }

    public function isLimitedResultsView(): bool
    {
        return \in_array($this->type, [
            DesignationTypeEnum::LOCAL_POLL,
            DesignationTypeEnum::LOCAL_ELECTION,
            DesignationTypeEnum::TERRITORIAL_ASSEMBLY,
            DesignationTypeEnum::COMMITTEE_SUPERVISOR,
            DesignationTypeEnum::CONSULTATION,
            DesignationTypeEnum::VOTE,
            DesignationTypeEnum::CONGRESS_CN,
        ], true);
    }

    public function initCreationDate(): void
    {
        if ($this->getVoteStartDate()) {
            $electionCreationDate = (clone $this->getVoteStartDate())->modify(\sprintf('-%d days', $this->isCommitteeSupervisorType() ? 15 : 2));

            if ($this->electionCreationDate !== $electionCreationDate) {
                $this->electionCreationDate = $electionCreationDate;
            }
        }
    }

    public function getFullAlertDescription(): ?string
    {
        $descriptionParts = [
            \sprintf(
                '%s du %s au %s.',
                match ($this->type) {
                    DesignationTypeEnum::CONSULTATION => 'La consultation sera ouverte',
                    DesignationTypeEnum::VOTE => 'Le vote sera ouvert',
                    default => 'L\'élection sera ouverte',
                },
                $this->voteStartDate?->format('d/m/Y à H:i'),
                $this->voteEndDate?->format('d/m/Y à H:i'),
            ),
            $this->alertDescription,
        ];

        return implode("\n\n", array_filter($descriptionParts));
    }

    public function getSortableAlertDate(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
