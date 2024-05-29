<?php

namespace App\Entity\VotingPlatform;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'voting_platform_election_pool')]
#[ORM\Entity]
class ElectionPool
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(length: 500)]
    private $code;

    /**
     * @var Election
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Election::class, inversedBy: 'electionPools')]
    private $election;

    /**
     * @var CandidateGroup[]|ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'electionPool', targetEntity: CandidateGroup::class, cascade: ['all'], orphanRemoval: true)]
    private $candidateGroups;

    /**
     * @var ElectionRound[]|ArrayCollection
     */
    #[ORM\ManyToMany(targetEntity: ElectionRound::class, mappedBy: 'electionPools', cascade: ['all'], orphanRemoval: true)]
    private $electionRounds;

    public function __construct(string $code)
    {
        $this->code = $code;
        $this->candidateGroups = new ArrayCollection();
        $this->electionRounds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getElection(): Election
    {
        return $this->election;
    }

    public function setElection(Election $election): void
    {
        $this->election = $election;
    }

    public function addCandidateGroup(CandidateGroup $candidateGroup): void
    {
        if (!$this->candidateGroups->contains($candidateGroup)) {
            $candidateGroup->setElectionPool($this);
            $this->candidateGroups->add($candidateGroup);
        }
    }

    /**
     * @return CandidateGroup[]
     */
    public function getCandidateGroups(): array
    {
        return $this->candidateGroups->toArray();
    }

    public function addElectionRound(ElectionRound $round): void
    {
        if (!$this->electionRounds->contains($round)) {
            $this->electionRounds->add($round);
        }
    }

    public function countCandidateGroups(): int
    {
        return $this->candidateGroups->count();
    }
}
