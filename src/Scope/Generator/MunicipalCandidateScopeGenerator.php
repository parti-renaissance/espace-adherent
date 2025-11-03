<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class MunicipalCandidateScopeGenerator extends AbstractScopeGenerator
{
    public function supports(Adherent $adherent): bool
    {
        return $adherent->isMunicipalCandidate();
    }

    public function getCode(): string
    {
        return ScopeEnum::MUNICIPAL_CANDIDATE;
    }
}
