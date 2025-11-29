<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform;

use App\Entity\EntityDesignationTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\ElectionResult\ElectionResult;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\Election\ElectionStatusEnum;
use App\VotingPlatform\Election\Enum\ElectionCancelReasonEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: ElectionRepository::class)]
#[ORM\Table(name: 'voting_platform_election')]
class Election
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityDesignationTrait {
        isVotePeriodActive as isDesignationVotePeriodActive;
        getRealVoteEndDate as getDesignationRealVoteEndDate;
    }

    /**
     * @var ElectionEntity
     */
    #[ORM\OneToOne(mappedBy: 'election', targetEntity: ElectionEntity::class, cascade: ['all'])]
    private $electionEntity;

    /**
     * @var string
     */
    #[ORM\Column]
    private $status = ElectionStatusEnum::OPEN;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $closedAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $canceledAt;

    #[ORM\Column(nullable: true, enumType: ElectionCancelReasonEnum::class)]
    private ?ElectionCancelReasonEnum $cancelReason = null;

    /**
     * @var ElectionRound[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'election', targetEntity: ElectionRound::class, cascade: ['all'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $electionRounds;

    /**
     * @var ElectionPool[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'election', targetEntity: ElectionPool::class, cascade: ['all'])]
    private $electionPools;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $secondRoundEndDate;

    /**
     * @var ElectionResult|null
     */
    #[ORM\OneToOne(mappedBy: 'election', targetEntity: ElectionResult::class, cascade: ['persist'])]
    private $electionResult;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    private $additionalPlaces;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $additionalPlacesGender;

    /**
     * @var VotersList|null
     */
    #[ORM\OneToOne(mappedBy: 'election', targetEntity: VotersList::class)]
    private $votersList;

    #[ORM\Column(type: 'smallint', options: ['default' => 0])]
    public int $notificationsSent = 0;

    public function __construct(
        Designation $designation,
        ?UuidInterface $uuid = null,
        array $rounds = [],
        ?ElectionEntity $entity = null,
    ) {
        $this->designation = $designation;
        $this->uuid = $uuid ?? Uuid::uuid4();

        $this->electionRounds = new ArrayCollection();
        $this->electionPools = new ArrayCollection();
        $this->electionEntity = $entity;

        $entity?->setElection($this);

        foreach ($rounds as $round) {
            $this->addElectionRound($round);
        }
    }

    public function getTitle(): string
    {
        return $this->designation->getTitle();
    }

    public function getElectionEntity(): ?ElectionEntity
    {
        return $this->electionEntity;
    }

    public function getElectionEntityName(): ?string
    {
        return $this->electionEntity?->getName();
    }

    public function setElectionEntity(ElectionEntity $electionEntity): void
    {
        $electionEntity->setElection($this);
        $this->electionEntity = $electionEntity;
    }

    public function getRealVoteEndDate(): \DateTime
    {
        return $this->secondRoundEndDate ?: $this->getDesignationRealVoteEndDate();
    }

    public function isVotePeriodActive(): bool
    {
        return $this->isOpen() && ($this->isDesignationVotePeriodActive() || $this->isSecondRoundVotePeriodActive());
    }

    public function isOpen(): bool
    {
        return ElectionStatusEnum::OPEN === $this->status;
    }

    public function isCanceled(): bool
    {
        return ElectionStatusEnum::CANCELED === $this->status;
    }

    public function cancel(ElectionCancelReasonEnum $reason): void
    {
        $this->status = ElectionStatusEnum::CANCELED;
        $this->canceledAt = new \DateTime();
        $this->cancelReason = $reason;

        $this->designation->cancel();
    }

    public function isClosed(): bool
    {
        return ElectionStatusEnum::CLOSED === $this->status;
    }

    public function close(): void
    {
        $this->status = ElectionStatusEnum::CLOSED;
        $this->closedAt = new \DateTime();
    }

    public function addElectionRound(ElectionRound $round): void
    {
        if (!$this->electionRounds->contains($round)) {
            $round->setElection($this);
            $this->electionRounds->add($round);
        }
    }

    public function addElectionPool(ElectionPool $pool): void
    {
        if (!$this->electionPools->contains($pool)) {
            $pool->setElection($this);
            $this->electionPools->add($pool);
        }
    }

    /**
     * @return ElectionPool[]
     */
    public function getElectionPools(): array
    {
        return $this->electionPools->toArray();
    }

    public function getCurrentRound(): ?ElectionRound
    {
        foreach ($this->electionRounds as $round) {
            if ($round->isActive()) {
                return $round;
            }
        }

        return null;
    }

    /**
     * @param ElectionPool[] $pools
     */
    public function startSecondRound(array $pools): void
    {
        $this->getCurrentRound()->disable();

        $this->addElectionRound($secondRound = new ElectionRound());
        $secondRound->setElectionPools($pools);

        $this->secondRoundEndDate = (clone $this->getVoteEndDate())->modify(
            \sprintf('+%d days', $this->getAdditionalRoundDuration())
        );
    }

    public function isSecondRoundVotePeriodActive(): bool
    {
        return null !== $this->secondRoundEndDate && (new \DateTime()) <= $this->secondRoundEndDate;
    }

    public function getSecondRoundEndDate(): ?\DateTime
    {
        return $this->secondRoundEndDate;
    }

    /**
     * @return ElectionRound[]
     */
    public function getElectionRounds(): array
    {
        return $this->electionRounds->toArray();
    }

    public function getFirstRound(): ?ElectionRound
    {
        return $this->electionRounds->first() ?? null;
    }

    public function getElectionResult(): ?ElectionResult
    {
        return $this->electionResult;
    }

    public function setElectionResult(?ElectionResult $electionResult): void
    {
        $this->electionResult = $electionResult;
    }

    public function hasResult(): bool
    {
        return null !== $this->electionResult;
    }

    public function canClose(): bool
    {
        if ($this->isClosed()) {
            return false;
        }

        $now = new \DateTime();

        if ($secondDate = $this->getSecondRoundEndDate()) {
            return $secondDate < $now;
        }

        if (!$this->electionResult) {
            return false;
        }

        $roundResult = $this->electionResult->getElectionRoundResult($this->getCurrentRound());

        if ($roundResult && !$this->designation->isSecondRoundEnabled()) {
            return true;
        }

        return $roundResult && $roundResult->hasOnlyElectedPool();
    }

    public function getClosedAt(): ?\DateTime
    {
        return $this->closedAt;
    }

    public function getAdditionalPlaces(): ?int
    {
        return $this->additionalPlaces;
    }

    public function setAdditionalPlaces(?int $additionalPlaces): void
    {
        $this->additionalPlaces = $additionalPlaces;
    }

    public function getAdditionalPlacesGender(): ?string
    {
        return $this->additionalPlacesGender;
    }

    public function setAdditionalPlacesGender(string $gender): void
    {
        $this->additionalPlacesGender = $gender;
    }

    public function getVotersList(): ?VotersList
    {
        return $this->votersList;
    }

    public function setVotersList(VotersList $list): void
    {
        $this->votersList = $list;
    }

    public function hasElected(): bool
    {
        return $this->hasResult() && \count($this->electionResult->getElectedCandidateGroups()) > 0;
    }

    public function isResultsDisplayable(): bool
    {
        if (!$this->hasResult()) {
            return false;
        }

        if (!$date = $this->closedAt ?? $this->secondRoundEndDate) {
            return true;
        }

        return $this->designation->getResultStartDate($date) <= new \DateTime();
    }

    public function markSentNotification(int $notification): void
    {
        $this->notificationsSent += $notification;
    }

    public function isNotificationAlreadySent(int $notification): bool
    {
        return 0 !== ($this->notificationsSent & $notification);
    }

    public function countCandidateGroups(): int
    {
        return array_sum($this->electionPools->map(fn (ElectionPool $pool) => $pool->countCandidateGroups())->toArray());
    }
}
