<?php

declare(strict_types=1);

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class PresidentDepartmentalAssemblyScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY;
    }
}
