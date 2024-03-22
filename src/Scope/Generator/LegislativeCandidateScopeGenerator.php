<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class LegislativeCandidateScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::LEGISLATIVE_CANDIDATE;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isLegislativeCandidate();
    }
}
