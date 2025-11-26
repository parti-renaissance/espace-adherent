<?php

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class SenatorScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::SENATOR;
    }
}
