<?php

namespace App\Entity\VotingPlatform;

use App\Entity\EntityDesignationTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\ElectionResult\ElectionResult;
use App\VotingPlatform\Election\ElectionStatusEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\VotingPlatform\ElectionRepository")
 *
 * @ORM\Table(name="voting_platform_election")
 */
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
     *
     * @ORM\OneToOne(targetEntity="App\Entity\VotingPlatform\ElectionEntity", mappedBy="election", cascade={"all"})
     */
    private $electionEntity;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $status = ElectionStatusEnum::OPEN;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closedAt;

    /**
     * @var ElectionRound[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\VotingPlatform\ElectionRound", mappedBy="election", cascade={"all"})
     * @ORM\OrderBy({"id": "ASC"})
     */
    private $electionRounds;

    /**
     * @var ElectionPool[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\VotingPlatform\ElectionPool", mappedBy="election", cascade={"all"})
     */
    private $electionPools;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $secondRoundEndDate;

    /**
     * @var ElectionResult|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\VotingPlatform\ElectionResult\ElectionResult", mappedBy="election", cascade={"persist"})
     */
    private $electionResult;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint", nullable=true, options={"unsigned": true})
     */
    private $additionalPlaces;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $additionalPlacesGender;

    /**
     * @var VotersList|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\VotingPlatform\VotersList", mappedBy="election")
     */
    private $votersList;

    public function __construct(
        Designation $designation,
        UuidInterface $uuid = null,
        array $rounds = [],
        ElectionEntity $entity = null
    ) {
        $this->designation = $designation;
        $this->uuid = $uuid ?? Uuid::uuid4();

        $this->electionRounds = new ArrayCollection();
        $this->electionPools = new ArrayCollection();
        $this->electionEntity = $entity;

        if ($entity) {
            $entity->setElection($this);
        }

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
        return $this->electionEntity ? $this->electionEntity->getName() : null;
    }

    public function setElectionEntity(ElectionEntity $electionEntity): void
    {
        $electionEntity->setElection($this);
        $this->electionEntity = $electionEntity;
    }

    public function getRealVoteEndDate(): \DateTime
    {
        return $this->secondRoundEndDate ? $this->secondRoundEndDate : $this->getDesignationRealVoteEndDate();
    }

    public function isVotePeriodActive(): bool
    {
        return $this->isOpen() && ($this->isDesignationVotePeriodActive() || $this->isSecondRoundVotePeriodActive());
    }

    public function isOpen(): bool
    {
        return ElectionStatusEnum::OPEN === $this->status;
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
            sprintf('+%d days', $this->getAdditionalRoundDuration())
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

    public function hasElected(): bool
    {
        return $this->hasResult() && \count($this->electionResult->getElectedCandidateGroups()) > 0;
    }

    public function isResultsDisplayable(): bool
    {
        if (!$this->hasResult()) {
            return false;
        }

        if (0 == $scheduleDelay = $this->designation->getResultScheduleDelay()) {
            return true;
        }

        if (!$date = $this->closedAt ?? $this->secondRoundEndDate) {
            return true;
        }

        return (clone $date)->modify((sprintf('+%d minutes', $scheduleDelay * 60))) <= new \DateTime();
    }
}
