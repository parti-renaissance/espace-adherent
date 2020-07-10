<?php

namespace App\VotingPlatform\Notifier\Event;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Election;

class CommitteeElectionVoteIsOpenEvent extends AbstractEvent
{
    private $committee;

    public function __construct(Adherent $adherent, Election $election, Committee $committee)
    {
        parent::__construct($adherent, $election);

        $this->committee = $committee;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }
}
