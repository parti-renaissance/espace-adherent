<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class ReferentScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::REFERENT;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isReferent();
    }

    public function getZones(Adherent $adherent): array
    {
        return $adherent->getManagedArea()->getZones()->toArray();
    }
}
