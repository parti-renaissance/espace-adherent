<?php

declare(strict_types=1);

namespace App\VotingPlatform\Event;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;

class CommitteeCandidacyEvent extends BaseCandidacyEvent
{
    private $committee;
    private $candidate;
    private $supervisors;

    public function __construct(
        CommitteeCandidacy $candidacy,
        Committee $committee,
        Adherent $candidate,
        array $supervisors = [],
    ) {
        parent::__construct($candidacy);

        $this->committee = $committee;
        $this->candidate = $candidate;
        $this->supervisors = $supervisors;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function getCandidate(): Adherent
    {
        return $this->candidate;
    }

    public function getSupervisors(): array
    {
        return $this->supervisors;
    }
}
