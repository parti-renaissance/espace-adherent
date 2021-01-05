<?php

namespace App\VotingPlatform\Notifier\Event;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Election;

class VotingPlatformVoteReminderEvent extends AbstractVotingPlatformElectionEvent
{
    private $adherent;

    public function __construct(Election $election, Adherent $adherent)
    {
        parent::__construct($election);

        $this->adherent = $adherent;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }
}
