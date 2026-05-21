<?php

declare(strict_types=1);

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class MilitantScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::MILITANT;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    protected function getZones(Adherent $adherent): array
    {
        return [];
    }
}
