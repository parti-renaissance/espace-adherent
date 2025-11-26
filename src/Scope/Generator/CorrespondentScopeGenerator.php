<?php

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class CorrespondentScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::CORRESPONDENT;
    }
}
