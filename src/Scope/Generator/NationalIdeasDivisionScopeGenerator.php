<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class NationalIdeasDivisionScopeGenerator extends AbstractScopeGenerator
{
    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasNationalIdeasDivisionRole();
    }

    public function getCode(): string
    {
        return ScopeEnum::NATIONAL_IDEAS_DIVISION;
    }
}
