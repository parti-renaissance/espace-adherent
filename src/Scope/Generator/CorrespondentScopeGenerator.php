<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class CorrespondentScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::CORRESPONDENT;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasZoneBasedRole(ScopeEnum::CORRESPONDENT);
    }

    protected function getZones(Adherent $adherent): array
    {
        return $adherent->findZoneBasedRole(ScopeEnum::CORRESPONDENT)->getZones()->toArray();
    }
}
