<?php

namespace App\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="voting_platform_election_pool")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ElectionPool
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $title;

    /**
     * @var Election
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\Election", inversedBy="electionPools")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $election;

    /**
     * @var CandidateGroup[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\VotingPlatform\CandidateGroup",
     *     mappedBy="electionPool",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $candidateGroups;

    /**
     * @var ElectionRound[]|ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="App\Entity\VotingPlatform\ElectionRound",
     *     mappedBy="electionPools",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $electionRounds;

    public function __construct(string $title)
    {
        $this->title = $title;
        $this->candidateGroups = new ArrayCollection();
        $this->electionRounds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
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
}
