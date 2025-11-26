<?php

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class DeputyScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::DEPUTY;
    }
}
