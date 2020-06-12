<?php

namespace App\Entity\VotingPlatform;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="voting_platform_vote_choice")
 *
 * @Algolia\Index(autoIndex=false)
 */
class VoteChoice
{
    public const BLANK_VOTE_VALUE = -1;

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var VoteResult
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\VoteResult", inversedBy="voteChoices")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $voteResult;

    /**
     * @var CandidateGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\CandidateGroup")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $candidateGroup;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isBlank = false;

    /**
     * @var ElectionPool
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\VotingPlatform\ElectionPool")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $electionPool;

    public function __construct(ElectionPool $electionPool)
    {
        $this->electionPool = $electionPool;
    }

    public function setVoteResult(VoteResult $voteResult): void
    {
        $this->voteResult = $voteResult;
    }

    public function setCandidateGroup(CandidateGroup $candidateGroup): void
    {
        $this->candidateGroup = $candidateGroup;
    }

    public function setIsBlank(bool $isBlank): void
    {
        $this->isBlank = $isBlank;
    }

    public function isBlank(): bool
    {
        return $this->isBlank;
    }

    public function getCandidateGroup(): ?CandidateGroup
    {
        return $this->candidateGroup;
    }

    public function getElectionPool(): ElectionPool
    {
        return $this->electionPool;
    }
}
