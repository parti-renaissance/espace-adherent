<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\AppEnum;
use App\Scope\Scope;

abstract class AbstractScopeGenerator implements ScopeGeneratorInterface
{
    final public function generate(Adherent $adherent): Scope
    {
        return new Scope(
            $this->getScope(),
            $this->getZones($adherent),
            [AppEnum::DATA_CORNER]
        );
    }

    abstract protected function getZones(Adherent $adherent): array;
}
