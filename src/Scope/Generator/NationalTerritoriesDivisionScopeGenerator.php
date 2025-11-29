<?php

declare(strict_types=1);

namespace App\Scope\Generator;

use App\Scope\ScopeEnum;

class NationalTerritoriesDivisionScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::NATIONAL_TERRITORIES_DIVISION;
    }
}
