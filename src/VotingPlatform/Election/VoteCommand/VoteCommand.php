<?php

namespace AppBundle\VotingPlatform\Election\VoteCommand;

use AppBundle\Entity\VotingPlatform\CandidateGroup;
use AppBundle\Entity\VotingPlatform\Election;
use AppBundle\VotingPlatform\Election\VoteCommandStateEnum;

class VoteCommand
{
    /**
     * @var string
     */
    private $state = VoteCommandStateEnum::INITIALISE;

    /**
     * @var CandidateGroup[]
     */
    private $candidateGroups = [];

    /**
     * @var Election
     */
    private $election;

    public function __construct(Election $election)
    {
        $this->election = $election;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState($state): void
    {
        $this->state = $state;
    }

    /**
     * @return CandidateGroup[]
     */
    public function getCandidateGroups(): array
    {
        return $this->candidateGroups;
    }

    /**
     * @param CandidateGroup[]
     */
    public function setCandidateGroups(array $candidateGroups): void
    {
        $this->candidateGroups = $candidateGroups;
    }

    public function isStart(): bool
    {
        return VoteCommandStateEnum::START === $this->state;
    }

    public function isVote(): bool
    {
        return VoteCommandStateEnum::VOTE === $this->state;
    }

    public function isConfirm(): bool
    {
        return VoteCommandStateEnum::CONFIRM === $this->state;
    }

    public function isFinish(): bool
    {
        return VoteCommandStateEnum::FINISH === $this->state;
    }

    public function getElection(): Election
    {
        return $this->election;
    }
}
