<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class ProcurationsManagerScopeGenerator extends AbstractScopeGenerator
{
    public function supports(Adherent $adherent): bool
    {
        return $adherent->isProcurationsManager();
    }

    public function getCode(): string
    {
        return ScopeEnum::PROCURATIONS_MANAGER;
    }
}
