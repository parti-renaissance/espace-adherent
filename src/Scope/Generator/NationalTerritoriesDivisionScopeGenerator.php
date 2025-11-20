<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class NationalTerritoriesDivisionScopeGenerator extends AbstractScopeGenerator
{
    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasNationalTerritoriesDivisionRole();
    }

    public function getCode(): string
    {
        return ScopeEnum::NATIONAL_TERRITORIES_DIVISION;
    }
}
