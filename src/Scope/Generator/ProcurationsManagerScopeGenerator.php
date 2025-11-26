<?php

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class ProcurationsManagerScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::PROCURATIONS_MANAGER;
    }
}
