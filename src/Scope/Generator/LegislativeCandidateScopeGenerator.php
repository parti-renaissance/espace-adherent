<?php

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class LegislativeCandidateScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::LEGISLATIVE_CANDIDATE;
    }
}
