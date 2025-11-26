<?php

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class RegionalDelegateScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::REGIONAL_DELEGATE;
    }
}
