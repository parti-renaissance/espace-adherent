<?php

namespace App\VotingPlatform\Event;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;

class CommitteeCandidacyEvent extends BaseCandidacyEvent
{
    private $committee;
    private $candidate;
    private $supervisor;

    public function __construct(
        CommitteeCandidacy $candidacy,
        Committee $committee,
        Adherent $candidate,
        Adherent $supervisor = null
    ) {
        parent::__construct($candidacy);

        $this->committee = $committee;
        $this->candidate = $candidate;
        $this->supervisor = $supervisor;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function getCandidate(): Adherent
    {
        return $this->candidate;
    }

    public function getSupervisor(): ?Adherent
    {
        return $this->supervisor;
    }
}
