<?php

declare(strict_types=1);

namespace App\VotingPlatform\Event;

use App\Entity\CommitteeCandidacy;
use Symfony\Contracts\EventDispatcher\Event;

class UpdateCommitteeCandidacyEvent extends Event
{
    private $committeeCandidacy;

    public function __construct(CommitteeCandidacy $candidacy)
    {
        $this->committeeCandidacy = $candidacy;
    }

    public function getCommitteeCandidacy(): CommitteeCandidacy
    {
        return $this->committeeCandidacy;
    }
}
