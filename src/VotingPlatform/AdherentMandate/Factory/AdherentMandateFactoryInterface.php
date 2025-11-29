<?php

declare(strict_types=1);

namespace App\VotingPlatform\AdherentMandate\Factory;

use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\Election;

interface AdherentMandateFactoryInterface
{
    public function create(Election $election, Candidate $candidate, string $quality): AdherentMandateInterface;

    public function supports(Election $election): bool;
}
