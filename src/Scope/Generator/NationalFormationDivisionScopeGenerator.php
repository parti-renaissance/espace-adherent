<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class NationalFormationDivisionScopeGenerator extends AbstractScopeGenerator
{
    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasNationalFormationDivisionRole();
    }

    public function getCode(): string
    {
        return ScopeEnum::NATIONAL_FORMATION_DIVISION;
    }
}
