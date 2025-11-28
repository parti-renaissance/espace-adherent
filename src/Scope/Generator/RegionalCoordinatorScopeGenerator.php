<?php

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class RegionalCoordinatorScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::REGIONAL_COORDINATOR;
    }
}
