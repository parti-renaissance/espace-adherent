<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class NationalElectedRepresentativesDivisionScopeGenerator extends AbstractScopeGenerator
{
    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasNationalElectedRepresentativesDivisionRole();
    }

    public function getCode(): string
    {
        return ScopeEnum::NATIONAL_ELECTED_REPRESENTATIVES_DIVISION;
    }
}
