<?php

declare(strict_types=1);

namespace App\VotingPlatform\Notifier\Event;

use App\Entity\VotingPlatform\Election;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractVotingPlatformElectionEvent extends Event
{
    private $election;

    public function __construct(Election $election)
    {
        $this->election = $election;
    }

    public function getElection(): Election
    {
        return $this->election;
    }
}
