<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform\ElectionResult;

use App\Entity\EntityIdentityTrait;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\VoteResult;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'voting_platform_election_round_result')]
class ElectionRoundResult
{
    use EntityIdentityTrait;

    /**
     * @var ElectionRound
     */
    #[ORM\OneToOne(targetEntity: ElectionRound::class)]
    private $electionRound;

    /**
     * @var ElectionResult
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ElectionResult::class, inversedBy: 'electionRoundResults')]
    private $electionResult;

    /**
     * @var ElectionPoolResult[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'electionRoundResult', targetEntity: ElectionPoolResult::class, cascade: ['all'])]
    private $electionPoolResults;

    public function __construct(ElectionRound $electionRound, ?UuidInterface $uuid = null)
    {
        $this->electionRound = $electionRound;
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->electionPoolResults = new ArrayCollection();
    }

    /**
     * @return ElectionPool[]
     */
    public function getNotElectedPools(): array
    {
        return $this->electionPoolResults
            ->filter(function (ElectionPoolResult $poolResult) { return !$poolResult->isElected(); })
            ->map(function (ElectionPoolResult $poolResult) { return $poolResult->getElectionPool(); })
            ->toArray()
        ;
    }

    /**
     * @return ElectionPoolResult[]
     */
    public function getElectedPoolResults(): array
    {
        return $this->electionPoolResults->filter(function (ElectionPoolResult $poolResult) {
            return $poolResult->isElected();
        })->toArray();
    }

    /**
     * @return CandidateGroupResult[]
     */
    public function getCandidateGroupResults(): array
    {
        return array_merge(...$this->electionPoolResults->map(function (ElectionPoolResult $poolResult) {
            return $poolResult->getCandidateGroupResults();
        })->toArray());
    }

    /**
     * @return ElectionPoolResult[]
     */
    public function getElectionPoolResults(): array
    {
        return $this->electionPoolResults->toArray();
    }

    public function getElectionRound(): ElectionRound
    {
        return $this->electionRound;
    }

    public function addElectionPoolResult(ElectionPoolResult $result): void
    {
        if (!$this->electionPoolResults->contains($result)) {
            $result->setElectionRoundResult($this);
            $this->electionPoolResults->add($result);
        }
    }

    public function updateFromNewVoteResult(VoteResult $voteResult): void
    {
        $expressedPoolIds = [];

        foreach ($voteResult->getVoteChoices() as $voteChoice) {
            $pool = $voteChoice->getElectionPool();
            $poolResult = $this->findElectedPoolResult($pool);

            $poolResult->updateFromNewVoteChoice($voteChoice);

            if (!\in_array($pool->getId(), $expressedPoolIds)) {
                if (!$voteChoice->isBlank()) {
                    $poolResult->incrementExpressed();
                }
                $expressedPoolIds[] = $pool->getId();
            }
        }
    }

    private function findElectedPoolResult(ElectionPool $pool): ?ElectionPoolResult
    {
        foreach ($this->electionPoolResults as $poolResult) {
            if ($poolResult->getElectionPool() === $pool) {
                return $poolResult;
            }
        }

        return null;
    }

    public function setElectionResult(ElectionResult $electionResult): void
    {
        $this->electionResult = $electionResult;
    }

    public function hasOnlyElectedPool(): bool
    {
        foreach ($this->electionPoolResults as $poolResult) {
            if (false === $poolResult->isElected()) {
                return false;
            }
        }

        return true;
    }

    public function getElectionResult(): ElectionResult
    {
        return $this->electionResult;
    }
}
