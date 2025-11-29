<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform\ElectionResult;

use App\Entity\EntityIdentityTrait;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\VoteChoice;
use App\VotingPlatform\Designation\MajorityVoteMentionEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'voting_platform_election_pool_result')]
class ElectionPoolResult
{
    use EntityIdentityTrait;

    /**
     * @var ElectionPool
     */
    #[ORM\ManyToOne(targetEntity: ElectionPool::class)]
    private $electionPool;

    /**
     * @var ElectionRoundResult
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ElectionRoundResult::class, inversedBy: 'electionPoolResults')]
    private $electionRoundResult;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $isElected = false;

    /**
     * @var CandidateGroupResult[]|Collection
     */
    #[Groups(['election_result'])]
    #[ORM\OneToMany(mappedBy: 'electionPoolResult', targetEntity: CandidateGroupResult::class, cascade: ['all'])]
    private $candidateGroupResults;

    /**
     * @var int
     */
    #[Groups(['election_result'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private $expressed = 0;

    /**
     * @var int
     */
    #[Groups(['election_result'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private $blank = 0;

    public function __construct(ElectionPool $pool, ?UuidInterface $uuid = null)
    {
        $this->electionPool = $pool;
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->candidateGroupResults = new ArrayCollection();
    }

    public function isElected(): bool
    {
        return $this->isElected;
    }

    public function addCandidateGroupResult(CandidateGroupResult $candidateGroupResult): void
    {
        if (!$this->candidateGroupResults->contains($candidateGroupResult)) {
            $candidateGroupResult->setElectionPoolResult($this);
            $this->candidateGroupResults->add($candidateGroupResult);
        }
    }

    public function getElectionPool(): ElectionPool
    {
        return $this->electionPool;
    }

    public function updateFromNewVoteChoice(VoteChoice $voteChoice): void
    {
        if ($this->electionPool->getElection()->getDesignation()->isMajorityType()) {
            $candidateGroupResult = $this->findCandidateGroupResult($voteChoice->getCandidateGroup());
            $candidateGroupResult->incrementMention($voteChoice->getMention());
        } else {
            if ($voteChoice->isBlank()) {
                ++$this->blank;
            } else {
                $candidateGroupResult = $this->findCandidateGroupResult($voteChoice->getCandidateGroup());
                $candidateGroupResult->increment();
            }
        }
    }

    public function incrementExpressed(): void
    {
        ++$this->expressed;
    }

    private function findCandidateGroupResult(CandidateGroup $candidateGroup): ?CandidateGroupResult
    {
        foreach ($this->candidateGroupResults as $candidateGroupResult) {
            if ($candidateGroupResult->getCandidateGroup() === $candidateGroup) {
                return $candidateGroupResult;
            }
        }

        return null;
    }

    public function setElectionRoundResult(ElectionRoundResult $electionRoundResult): void
    {
        $this->electionRoundResult = $electionRoundResult;
    }

    public function getExpressed(bool $withBlank = false): int
    {
        return $this->expressed + ($withBlank ? $this->blank : 0);
    }

    public function getBlank(): int
    {
        return $this->blank;
    }

    public function getBlankRate(): float
    {
        $total = $this->getExpressed(true);

        if ($total > 0) {
            return round($this->blank * 100.0 / $total, 2);
        }

        return 0;
    }

    #[Groups(['election_result'])]
    public function getParticipated(): int
    {
        return $this->electionRoundResult->getElectionResult()->getParticipated();
    }

    #[Groups(['election_result'])]
    public function getAbstentions(): int
    {
        return $this->getParticipated() - $this->getBulletinCount();
    }

    #[Groups(['election_result'])]
    public function getBulletinCount(): int
    {
        return $this->expressed + $this->blank;
    }

    /**
     * @return CandidateGroup[]
     */
    public function getElectedCandidateGroups(): array
    {
        return array_map(function (CandidateGroupResult $result) {
            return $result->getCandidateGroup();
        }, $this->getElectedCandidateGroupResults());
    }

    /**
     * @return CandidateGroupResult[]
     */
    public function getElectedCandidateGroupResults(): array
    {
        return $this->candidateGroupResults
            ->filter(function (CandidateGroupResult $candidateGroupResult) {
                return $candidateGroupResult->getCandidateGroup()->isElected();
            })
            ->toArray()
        ;
    }

    public function getCandidateGroupResultsSorted(): array
    {
        if ($this->electionPool->getElection()->getDesignation()->isMajorityType()) {
            $candidateGroupResults = $this->candidateGroupResults->toArray();

            usort($candidateGroupResults, function (CandidateGroupResult $a, CandidateGroupResult $b) {
                if ($a->getCandidateGroup()->isElected()) {
                    return -1;
                }

                if ($b->getCandidateGroup()->isElected()) {
                    return 1;
                }

                return array_search($a->getMajorityMention(), MajorityVoteMentionEnum::ALL) <=> array_search($b->getMajorityMention(), MajorityVoteMentionEnum::ALL);
            });

            return $candidateGroupResults;
        }

        return $this->candidateGroupResults->matching(Criteria::create()->orderBy(['total' => 'DESC']))->toArray();
    }

    /**
     * @return CandidateGroupResult[]
     */
    public function getCandidateGroupResults(): array
    {
        return $this->candidateGroupResults->toArray();
    }

    /**
     * @return Candidate[]
     */
    public function getAdditionallyElectedCandidates(): array
    {
        $candidates = [];

        foreach ($this->candidateGroupResults as $candidateGroupResult) {
            if ($candidateGroupResult->getCandidateGroup()->isElected()) {
                continue;
            }

            foreach ($candidateGroupResult->getCandidateGroup()->getCandidates() as $candidate) {
                if ($candidate->isAdditionallyElected()) {
                    $candidates[] = $candidate;
                }
            }
        }

        return $candidates;
    }

    public function setIsElected(bool $value): void
    {
        $this->isElected = $value;
    }
}
