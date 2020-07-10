<?php

namespace App\VotingPlatform\Notifier\Event;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Designation\Designation;

abstract class AbstractCommitteeElectionEvent extends AbstractEvent
{
    private $committee;

    public function __construct(Adherent $adherent, Designation $designation, Committee $committee)
    {
        parent::__construct($adherent, $designation);

        $this->committee = $committee;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }
}
