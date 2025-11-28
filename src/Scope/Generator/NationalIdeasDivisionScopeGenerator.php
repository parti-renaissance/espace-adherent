<?php

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class NationalIdeasDivisionScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::NATIONAL_IDEAS_DIVISION;
    }
}
