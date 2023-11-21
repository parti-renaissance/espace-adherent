<?php

namespace App\Entity\VotingPlatform\Designation;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\Filter\InZoneOfScopeFilter;
use App\Api\Validator\UpdateDesignationGroupGenerator;
use App\Collection\ZoneCollection;
use App\Entity\CmsBlock;
use App\Entity\EntityAdherentBlameableInterface;
use App\Entity\EntityAdherentBlameableTrait;
use App\Entity\EntityAdministratorBlameableInterface;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityZoneTrait;
use App\Entity\ReferentTag;
use App\Entity\VotingPlatform\Designation\CandidacyPool\CandidacyPool;
use App\Entity\VotingPlatform\Designation\Poll\Poll;
use App\Entity\VotingPlatform\ElectionPoolCodeEnum;
use App\Entity\ZoneableEntity;
use App\VotingPlatform\Designation\CreatePartialDesignationCommand;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     routePrefix="/v3",
 *     attributes={
 *         "order": {"voteStartDate": "DESC"},
 *         "normalization_context": {
 *             "groups": {"designation_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"designation_write"},
 *         },
 *         "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'designation')"
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/designations/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'designation')"
 *         },
 *         "put": {
 *             "path": "/designations/{uuid}",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'designation')",
 *             "validation_groups": UpdateDesignationGroupGenerator::class,
 *         },
 *         "cancel": {
 *             "path": "/designations/{uuid}/cancel",
 *             "method": "PUT",
 *             "requirements": {"uuid": "%pattern_uuid%"},
 *             "defaults": {"_api_receive": false},
 *             "security": "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'designation')",
 *             "controller": "App\Controller\Api\VotingPlatform\CancelElectionController",
 *         },
 *     },
 *     collectionOperations={
 *         "get": {
 *             "normalization_context": {
 *                 "groups": {"designation_list"}
 *             }
 *         },
 *         "post": {
 *             "validation_groups": {"api_designation_write"},
 *         }
 *     }
 * )
 *
 * @ApiFilter(InZoneOfScopeFilter::class)
 *
 * @ORM\Entity(repositoryClass="App\Repository\VotingPlatform\DesignationRepository")
 */
class Designation implements EntityAdministratorBlameableInterface, EntityAdherentBlameableInterface, ZoneableEntity
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
        'Rappel de vote' => self::NOTIFICATION_VOTE_REMINDER,
        'Ouverture du tour bis' => self::NOTIFICATION_SECOND_ROUND,
    ];

    public const NOTIFICATION_VOTE_OPENED = 1;
    public const NOTIFICATION_VOTE_CLOSED = 2;
    public const NOTIFICATION_VOTE_REMINDER = 4;
    public const NOTIFICATION_SECOND_ROUND = 8;
    public const NOTIFICATION_RESULT_READY = 16;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    #[Assert\NotBlank(groups: ['Admin'])]
    private $label;

    /**
     * @ORM\Column(nullable=true)
     */
    #[Assert\NotBlank(groups: ['api_designation_write', 'api_designation_write_limited'])]
    #[Groups(['designation_read', 'designation_write', 'designation_list', 'designation_write_limited', 'committee_election:read'])]
    public ?string $customTitle = null;

    /**
     * @var string|null
     *
     * @ORM\Column
     */
    #[Assert\NotBlank(groups: ['Default', 'api_designation_write'])]
    #[Assert\Choice(choices: DesignationTypeEnum::MAIN_TYPES, groups: ['Default'])]
    #[Assert\Choice(choices: DesignationTypeEnum::API_AVAILABLE_TYPES, groups: ['api_designation_write'])]
    #[Groups(['designation_read', 'designation_write', 'designation_list'])]
    private $type;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $globalZones;

    /**
     * @var ReferentTag[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag")
     */
    private $referentTags;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $candidacyStartDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $candidacyEndDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Groups(['designation_read', 'committee_election:read'])]
    public ?\DateTime $electionCreationDate = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Groups(['designation_read', 'designation_write', 'designation_list', 'committee_election:read'])]
    #[Assert\GreaterThan('now', message: 'La date de début doit être dans le futur.', groups: ['Admin_creation', 'api_designation_write'])]
    private $voteStartDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    #[Groups(['designation_read', 'designation_write', 'committee_election:read'])]
    #[Assert\Expression('value > this.getVoteStartDate()', message: 'La date de clôture doit être postérieur à la date de début', groups: ['Default', 'api_designation_write'])]
    private $voteEndDate;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    private $resultDisplayDelay = 14;

    /**
     * Display the election results after this delay (in hours)
     *
     * @var float
     *
     * @ORM\Column(type="float", options={"unsigned": true, "default": 0})
     */
    #[Assert\GreaterThanOrEqual(0)]
    private $resultScheduleDelay = 0;

    /**
     * Duration of the additional round in day
     *
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    #[Assert\NotBlank]
    #[Assert\GreaterThan(0)]
    private $additionalRoundDuration = 5;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    #[Assert\NotBlank]
    #[Assert\GreaterThanOrEqual(0)]
    private $lockPeriodThreshold = 3;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $limited = false;

    /**
     * @var string
     *
     * @ORM\Column(options={"default": self::DENOMINATION_DESIGNATION})
     */
    private $denomination = self::DENOMINATION_DESIGNATION;

    /**
     * @var array|null
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $pools;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    #[Groups(['designation_read', 'designation_write', 'designation_write_limited'])]
    private ?string $description = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $notifications = 15;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isBlankVoteEnabled = true;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\Designation\Poll\Poll")
     */
    #[Assert\Expression('!(this.isLocalPollType() || this.isConsultationType()) or value', message: 'Vous devez préciser le questionnaire qui sera utilisé pour cette élection.')]
    public ?Poll $poll = null;

    /**
     * @var CandidacyPool[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\VotingPlatform\Designation\CandidacyPool\CandidacyPool", mappedBy="designation", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $candidacyPools;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CmsBlock")
     */
    public ?CmsBlock $wordingWelcomePage = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CmsBlock")
     */
    public ?CmsBlock $wordingRegulationPage = null;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"unsigned": true})
     */
    #[Assert\Expression('!this.isLocalElectionType() or value', message: 'Vous devez préciser le nombre des sièges à distribuer.')]
    public ?int $seats = null;

    /**
     * @ORM\Column(type="smallint", nullable=true, options={"unsigned": true})
     */
    #[Assert\GreaterThan(0)]
    #[Assert\LessThanOrEqual(100)]
    public ?int $majorityPrime = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    #[Assert\Expression('!this.isLocalElectionType() or !this.majorityPrime or null != value', message: "Vous devez préciser le mode d'arrondi pour la prime majoritaire.")]
    public ?bool $majorityPrimeRoundSupMode = null;

    /**
     * @ORM\Column(type="uuid", nullable=true)
     */
    #[Assert\Expression('!this.isCommitteeSupervisorType() or value', message: 'Un identifiant est requis pour ce champs.', groups: ['api_designation_write'])]
    #[Groups(['designation_read', 'designation_write'])]
    private ?UuidInterface $electionEntityIdentifier = null;

    /** @ORM\Column(type="boolean", options={"default": false}) */
    private bool $isCanceled = false;

    public function __construct(string $label = null, UuidInterface $uuid = null)
    {
        $this->label = $label;
        $this->uuid = $uuid ?? Uuid::uuid4();

        $this->referentTags = new ArrayCollection();
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

    /**
     * @return ReferentTag[]
     */
    public function getReferentTags(): array
    {
        return $this->referentTags->toArray();
    }

    public function addReferentTag(ReferentTag $tag): void
    {
        if (!$this->referentTags->contains($tag)) {
            $this->referentTags->add($tag);
        }
    }

    public function removeReferentTag(ReferentTag $tag): void
    {
        $this->referentTags->removeElement($tag);
    }

    public function setReferentTags(array $referentTags): void
    {
        $this->referentTags->clear();

        foreach ($referentTags as $tag) {
            $this->addReferentTag($tag);
        }
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

    public function getResultStartDate(\DateTime $voteEndDate = null): \DateTime
    {
        $date = $voteEndDate ?? $this->voteEndDate;

        if (!$this->resultScheduleDelay || !$date) {
            return $date;
        }

        return (clone $date)->modify(sprintf('+%d minutes', \intval($this->resultScheduleDelay * 60)));
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
                return sprintf('%s_%s', $this->type, strtolower($suffix));
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

    public function isNotificationVoteOpenedEnabled(): bool
    {
        return $this->isNotificationEnabled(self::NOTIFICATION_VOTE_OPENED);
    }

    public function isNotificationVoteClosedEnabled(): bool
    {
        return $this->isNotificationEnabled(self::NOTIFICATION_VOTE_CLOSED);
    }

    public function isNotificationVoteReminderEnabled(): bool
    {
        return $this->isNotificationEnabled(self::NOTIFICATION_VOTE_REMINDER);
    }

    public function isNotificationSecondRoundEnabled(): bool
    {
        return $this->isNotificationEnabled(self::NOTIFICATION_SECOND_ROUND);
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
            DesignationTypeEnum::TERRITORIAL_ASSEMBLY,
        ], true)) {
            return true;
        }

        // no need to have a zone for committee partial elections
        if ($this->isCommitteeTypes() && $this->isLimited()) {
            return true;
        }

        return
            ($this->isCommitteeTypes() && !empty($this->globalZones))
            || ($this->isCopolType() && !$this->referentTags->isEmpty())
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

        return (clone $this->getVoteEndDate())->modify(sprintf('+%d days', $this->getResultDisplayDelay()));
    }

    public function markAsLimited(): void
    {
        $this->limited = true;
    }

    public function __clone()
    {
        $this->id = null;
        $this->uuid = Uuid::uuid4();
        $this->referentTags = new ArrayCollection();
    }

    public function getPoolTypes(): array
    {
        switch ($this->getType()) {
            case DesignationTypeEnum::COMMITTEE_ADHERENT:
                return ElectionPoolCodeEnum::COMMITTEE_ADHERENT;
            case DesignationTypeEnum::COPOL:
                return ElectionPoolCodeEnum::COPOL;
        }

        return [];
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

    public function isCopolType(): bool
    {
        return \in_array($this->type, [DesignationTypeEnum::COPOL, DesignationTypeEnum::NATIONAL_COUNCIL], true);
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

    public function isTerritorialAssemblyType(): bool
    {
        return DesignationTypeEnum::TERRITORIAL_ASSEMBLY === $this->type;
    }

    public function isExecutiveOfficeType(): bool
    {
        return DesignationTypeEnum::EXECUTIVE_OFFICE === $this->type;
    }

    public function isMajorityType(): bool
    {
        return DesignationTypeEnum::NATIONAL_COUNCIL === $this->type;
    }

    public function isRenaissanceElection(): bool
    {
        return \in_array($this->type, DesignationTypeEnum::RENAISSANCE_TYPES, true);
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
            && !$this->isTerritorialAssemblyType();
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
        return !$this->isLocalElectionTypes();
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

    public function isLimitedResultsView(): bool
    {
        return \in_array($this->type, [
            DesignationTypeEnum::LOCAL_POLL,
            DesignationTypeEnum::LOCAL_ELECTION,
            DesignationTypeEnum::TERRITORIAL_ASSEMBLY,
            DesignationTypeEnum::COMMITTEE_SUPERVISOR,
        ], true);
    }
}
