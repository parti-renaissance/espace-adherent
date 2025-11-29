<?php

declare(strict_types=1);

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class RegionalCoordinatorScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::REGIONAL_COORDINATOR;
    }
}
