<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class MunicipalPilotScopeGenerator extends AbstractScopeGenerator
{
    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasZoneBasedRole($this->getCode());
    }

    public function getCode(): string
    {
        return ScopeEnum::MUNICIPAL_PILOT;
    }
}
