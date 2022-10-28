<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class SupervisorScopeGenerator extends AbstractScopeGenerator
{
    protected function getZones(Adherent $adherent): array
    {
        $zones = [];
        foreach ($adherent->getSupervisorMandates() as $mandate) {
            $zones = array_merge($zones, $mandate->getCommittee()->getZones()->toArray());
        }

        return $zones;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isSupervisor();
    }

    public function getCode(): string
    {
        return ScopeEnum::SUPERVISOR;
    }
}
