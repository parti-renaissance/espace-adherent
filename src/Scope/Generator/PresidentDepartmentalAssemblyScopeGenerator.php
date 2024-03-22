<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class PresidentDepartmentalAssemblyScopeGenerator extends AbstractScopeGenerator
{
    public function supports(Adherent $adherent): bool
    {
        return $adherent->isPresidentDepartmentalAssembly();
    }

    public function getCode(): string
    {
        return ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY;
    }
}
