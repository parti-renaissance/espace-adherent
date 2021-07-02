<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\AppEnum;
use App\Scope\Scope;
use App\Scope\ScopeEnum;

abstract class AbstractScopeGenerator implements ScopeGeneratorInterface
{
    final public function generate(Adherent $adherent): Scope
    {
        $code = $this->getScope();

        return new Scope(
            $code,
            $this->getScopeName($code),
            $this->getZones($adherent),
            [AppEnum::DATA_CORNER]
        );
    }

    abstract protected function getZones(Adherent $adherent): array;

    private function getScopeName(string $code): string
    {
        return ScopeEnum::LABELS[$code] ?? $code;
    }
}
