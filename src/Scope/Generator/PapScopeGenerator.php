<?php

declare(strict_types=1);

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class PapScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::PAP;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasPapUserRole();
    }

    protected function getZones(Adherent $adherent): array
    {
        return [];
    }
}
