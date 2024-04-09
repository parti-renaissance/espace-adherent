<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class FdeCoordinatorScopeGenerator extends AbstractScopeGenerator
{
    public function supports(Adherent $adherent): bool
    {
        return $adherent->isFdeCoordinator();
    }

    public function getCode(): string
    {
        return ScopeEnum::FDE_COORDINATOR;
    }
}
