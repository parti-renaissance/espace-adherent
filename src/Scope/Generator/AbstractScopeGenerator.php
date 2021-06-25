<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\Scope;

abstract class AbstractScopeGenerator implements ScopeGeneratorInterface
{
    abstract public function generate(Adherent $adherent): Scope;

    abstract public function supports(Adherent $adherent): bool;

    abstract public function getScope(): string;

    public function supportsScope(string $scope, Adherent $adherent): bool
    {
        return $this->getScope() === $scope && $this->supports($adherent);
    }
}
