<?php

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class NationalFormationDivisionScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::NATIONAL_FORMATION_DIVISION;
    }
}
