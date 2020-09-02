<?php

namespace App\VotingPlatform\Notifier\Event;

use App\Entity\VotingPlatform\Election;
use Symfony\Component\EventDispatcher\Event;

class VotingPlatformElectionVoteIsOpenEvent extends Event
{
    private $election;
    private $adherents;

    public function __construct(Election $election, array $adherents)
    {
        $this->election = $election;
        $this->adherents = $adherents;
    }

    public function getElection(): Election
    {
        return $this->election;
    }

    public function getAdherents(): array
    {
        return $this->adherents;
    }
}
