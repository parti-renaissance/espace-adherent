<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class SenatorScopeGenerator extends AbstractScopeGenerator
{
    public function getScope(): string
    {
        return ScopeEnum::SENATOR;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isSenator();
    }

    protected function getZones(Adherent $adherent): array
    {
        return [$adherent->getSenatorArea()->getDepartmentTag()->getZone()];
    }
}
