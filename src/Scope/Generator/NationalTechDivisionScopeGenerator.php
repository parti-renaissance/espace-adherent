<?php

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class NationalTechDivisionScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::NATIONAL_TECH_DIVISION;
    }
}
