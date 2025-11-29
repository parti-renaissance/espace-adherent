<?php

declare(strict_types=1);

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class MunicipalCandidateScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::MUNICIPAL_CANDIDATE;
    }
}
