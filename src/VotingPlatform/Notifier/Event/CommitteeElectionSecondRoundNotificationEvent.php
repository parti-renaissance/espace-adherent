<?php

namespace App\VotingPlatform\Notifier\Event;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;

class CommitteeElectionSecondRoundNotificationEvent extends AbstractCommitteeElectionEvent
{
    protected $election;

    public function __construct(Adherent $adherent, Designation $designation, Committee $committee, Election $election)
    {
        parent::__construct($adherent, $designation, $committee);

        $this->election = $election;
    }

    public function getElection(): Election
    {
        return $this->election;
    }
}
