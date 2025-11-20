<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class NationalTechDivisionScopeGenerator extends AbstractScopeGenerator
{
    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasNationalTechDivisionRole();
    }

    public function getCode(): string
    {
        return ScopeEnum::NATIONAL_TECH_DIVISION;
    }
}
