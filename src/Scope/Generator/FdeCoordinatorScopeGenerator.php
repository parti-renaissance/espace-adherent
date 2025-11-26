<?php

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class FdeCoordinatorScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::FDE_COORDINATOR;
    }
}
