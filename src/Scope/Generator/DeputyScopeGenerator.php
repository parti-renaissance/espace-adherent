<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\AppEnum;
use App\Scope\Scope;
use App\Scope\ScopeEnum;

class DeputyScopeGenerator implements ScopeGeneratorInterface
{
    public function generate(Adherent $adherent): Scope
    {
        return new Scope(
            ScopeEnum::DEPUTY,
            [$adherent->getManagedDistrict()->getReferentTag()->getZone()],
            [AppEnum::DATA_CORNER]
        );
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->isDeputy();
    }
}
