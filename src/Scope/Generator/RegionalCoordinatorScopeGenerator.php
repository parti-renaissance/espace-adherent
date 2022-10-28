<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class RegionalCoordinatorScopeGenerator extends AbstractScopeGenerator
{
    protected function getZones(Adherent $adherent): array
    {
        return $adherent->getRegionalCoordinatorZone();
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isRegionalCoordinator();
    }

    public function getCode(): string
    {
        return ScopeEnum::REGIONAL_COORDINATOR;
    }
}
