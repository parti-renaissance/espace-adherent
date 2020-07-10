<?php

namespace App\VotingPlatform\Notifier\Event;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;
use Symfony\Component\EventDispatcher\Event;

class AbstractEvent extends Event implements ElectionNotifyEventInterface
{
    private $adherent;
    private $election;

    public function __construct(Adherent $adherent, Election $election)
    {
        $this->adherent = $adherent;
        $this->election = $election;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getElection(): Election
    {
        return $this->election;
    }
}
