<?php

declare(strict_types=1);

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class CandidateScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::CANDIDATE;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isHeadedRegionalCandidate();
    }

    protected function getZones(Adherent $adherent): array
    {
        return [$adherent->getCandidateManagedArea()->getZone()];
    }
}
