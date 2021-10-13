<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class PhoningNationalManagerScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::PHONING_NATIONAL_MANAGER;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasTeamPhoningNationalManagerRole();
    }

    protected function getZones(Adherent $adherent): array
    {
        return [];
    }
}
