<?php

namespace App\Entity\VotingPlatform\ElectionResult;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityIdentityTrait;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\VoteChoice;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="voting_platform_election_pool_result")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ElectionPoolResult
{
    use EntityIdentityTrait;

    /**
     * @var ElectionPool
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\ElectionPool")
     */
    private $electionPool;

    /**
     * @var ElectionRoundResult
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\ElectionResult\ElectionRoundResult", inversedBy="electionPoolResults")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $electionRoundResult;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isElected = false;

    /**
     * @var CandidateGroupResult[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\VotingPlatform\ElectionResult\CandidateGroupResult", mappedBy="electionPoolResult", cascade={"all"})
     */
    private $candidateGroupResults;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    private $expressed = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    private $blank = 0;

    public function __construct(ElectionPool $pool, UuidInterface $uuid = null)
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
        if ($voteChoice->isBlank()) {
            ++$this->blank;
        } else {
            ++$this->expressed;

            $candidateGroupResult = $this->findCandidateGroupResult($voteChoice->getCandidateGroup());
            $candidateGroupResult->increment();
        }
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

    public function sync(): void
    {
        $max = 0;
        $elected = null;

        foreach ($this->candidateGroupResults as $result) {
            $total = $result->getTotal();

            if ($total > $max) {
                $max = $total;
                $elected = $result->getCandidateGroup();
            } elseif ($max === $total) {
                $elected = null;
            }
        }

        if ($elected) {
            $this->isElected = true;
            $elected->setElected(true);
        }
    }

    public function getExpressed(): int
    {
        return $this->expressed;
    }

    public function getBlank(): int
    {
        return $this->blank;
    }

    public function getParticipated(): int
    {
        return $this->electionRoundResult->getElectionResult()->getParticipated();
    }

    public function getAbstentions(): int
    {
        return $this->getParticipated() - $this->bulletinCount();
    }

    public function bulletinCount(): int
    {
        return $this->expressed + $this->blank;
    }

    public function getElectedCandidateGroupResult(): ?CandidateGroupResult
    {
        foreach ($this->candidateGroupResults as $groupResult) {
            if ($groupResult->getCandidateGroup()->isElected()) {
                return $groupResult;
            }
        }

        return null;
    }

    public function getCandidateGroupResultsSorted(): array
    {
        return $this->candidateGroupResults->matching(Criteria::create()->orderBy(['total' => 'DESC']))->toArray();
    }
}
