<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class RegionalDelegateScopeGenerator extends AbstractScopeGenerator
{
    protected function getZones(Adherent $adherent): array
    {
        return $adherent->getRegionalDelegateZones();
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isRegionalDelegate();
    }

    public function getCode(): string
    {
        return ScopeEnum::REGIONAL_DELEGATE;
    }
}
