<?php

namespace AppBundle\VotingPlatform\Event;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeElection;
use Symfony\Component\EventDispatcher\Event;

class CommitteeCandidacyEvent extends Event
{
    private $election;
    private $candidate;
    private $supervisor;

    public function __construct(CommitteeElection $election, Adherent $candidate, Adherent $supervisor = null)
    {
        $this->election = $election;
        $this->candidate = $candidate;
        $this->supervisor = $supervisor;
    }

    public function getElection(): CommitteeElection
    {
        return $this->election;
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
