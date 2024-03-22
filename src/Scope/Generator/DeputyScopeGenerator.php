<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

class DeputyScopeGenerator extends AbstractScopeGenerator
{
    public function getCode(): string
    {
        return ScopeEnum::DEPUTY;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isDeputy();
    }
}
