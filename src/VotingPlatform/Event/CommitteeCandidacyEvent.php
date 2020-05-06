<?php

namespace App\VotingPlatform\Event;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use Symfony\Component\EventDispatcher\Event;

class CommitteeCandidacyEvent extends Event
{
    private $committeeCandidacy;
    private $committee;
    private $candidate;
    private $supervisor;

    public function __construct(
        CommitteeCandidacy $candidacy,
        Committee $committee,
        Adherent $candidate,
        Adherent $supervisor = null
    ) {
        $this->committeeCandidacy = $candidacy;
        $this->committee = $committee;
        $this->candidate = $candidate;
        $this->supervisor = $supervisor;
    }

    public function getCommitteeCandidacy(): CommitteeCandidacy
    {
        return $this->committeeCandidacy;
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
