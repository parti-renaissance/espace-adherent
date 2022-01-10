<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class JeMengageAdminScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::JEMENGAGE_ADMIN;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasZoneBasedRole(ScopeEnum::JEMENGAGE_ADMIN);
    }

    protected function getZones(Adherent $adherent): array
    {
        return $adherent->findZoneBasedRole(ScopeEnum::JEMENGAGE_ADMIN)->getZones()->toArray();
    }
}
