<?php

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class NationalElectedRepresentativesDivisionScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::NATIONAL_ELECTED_REPRESENTATIVES_DIVISION;
    }
}
