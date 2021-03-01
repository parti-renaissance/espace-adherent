<?php

namespace App\VotingPlatform\Election\Event;

use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Voter;
use Symfony\Contracts\EventDispatcher\Event;

class NewVote extends Event
{
    private $election;
    private $voter;

    public function __construct(Election $election, Voter $voter)
    {
        $this->election = $election;
        $this->voter = $voter;
    }

    public function getElection(): Election
    {
        return $this->election;
    }

    public function getVoter(): Voter
    {
        return $this->voter;
    }
}
