<?php

declare(strict_types=1);

namespace App\Entity\VotingPlatform\ElectionResult;

use App\Entity\EntityIdentityTrait;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\ElectionRound;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'voting_platform_election_result')]
class ElectionResult
{
    use EntityIdentityTrait;

    /**
     * @var Election
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'electionResult', targetEntity: Election::class)]
    private $election;

    /**
     * @var ElectionRoundResult[]|Collection
     */
    #[Groups(['election_result'])]
    #[ORM\OneToMany(mappedBy: 'electionResult', targetEntity: ElectionRoundResult::class, cascade: ['all'])]
    private $electionRoundResults;

    /**
     * @var int
     */
    #[Groups(['election_result'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    private $participated = 0;

    public function __construct(Election $election, ?UuidInterface $uuid = null)
    {
        $this->election = $election;
        $this->uuid = $uuid ?? Uuid::uuid4();

        $this->electionRoundResults = new ArrayCollection();
    }

    public function alreadyFilledForRound(ElectionRound $electionRound): bool
    {
        return null !== $this->getElectionRoundResult($electionRound);
    }

    public function addElectionRoundResult(ElectionRoundResult $result): void
    {
        if (!$this->electionRoundResults->contains($result)) {
            $result->setElectionResult($this);
            $this->electionRoundResults->add($result);
        }
    }

    public function getElectionRoundResult(ElectionRound $electionRound): ?ElectionRoundResult
    {
        foreach ($this->electionRoundResults as $result) {
            if ($result->getElectionRound() === $electionRound) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @return ElectionPoolResult[]
     */
    public function getElectedPoolResults(): array
    {
        $electedPoolResults = [];

        foreach ($this->electionRoundResults as $roundResult) {
            if ($elected = $roundResult->getElectedPoolResults()) {
                array_push($electedPoolResults, ...$elected);
            }
        }

        return $electedPoolResults;
    }

    /**
     * @return CandidateGroup[]
     */
    public function getElectedCandidateGroups(): array
    {
        $electedCandidateGroups = array_map(function (ElectionPoolResult $result) {
            return $result->getElectedCandidateGroups();
        }, $this->getElectedPoolResults());

        if ($electedCandidateGroups) {
            return array_merge(...$electedCandidateGroups);
        }

        return [];
    }

    /**
     * @return CandidateGroupResult[]
     */
    public function getCandidateGroupResults(): array
    {
        return array_merge(...$this->electionRoundResults->map(function (ElectionRoundResult $result) {
            return $result->getCandidateGroupResults();
        })->toArray());
    }

    /**
     * @return ElectionPool[]
     */
    public function getNotElectedPools(ElectionRound $currentRound): array
    {
        return $this->getElectionRoundResult($currentRound)->getNotElectedPools();
    }

    public function getParticipated(): int
    {
        return $this->participated;
    }

    public function setParticipated(int $participated): void
    {
        $this->participated = $participated;
    }
}
