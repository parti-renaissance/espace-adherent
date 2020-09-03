<?php

namespace App\Entity\VotingPlatform\ElectionResult;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\EntityIdentityTrait;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionPool;
use App\Entity\VotingPlatform\ElectionRound;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="voting_platform_election_result")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ElectionResult
{
    use EntityIdentityTrait;

    /**
     * @var Election
     *
     * @ORM\OneToOne(targetEntity="App\Entity\VotingPlatform\Election", inversedBy="electionResult")
     */
    private $election;

    /**
     * @var ElectionRoundResult[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\VotingPlatform\ElectionResult\ElectionRoundResult", mappedBy="electionResult", cascade={"all"})
     */
    private $electionRoundResults;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned": true, "default": 0})
     */
    private $participated = 0;

    public function __construct(Election $election, UuidInterface $uuid = null)
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
            array_push($electedPoolResults, ...$roundResult->getElectedPoolResults());
        }

        return $electedPoolResults;
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
