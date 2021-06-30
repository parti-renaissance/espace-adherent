<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Scope\Scope;

interface ScopeGeneratorInterface
{
    public function generate(Adherent $adherent): Scope;

    public function supports(Adherent $adherent): bool;

    public function getScope(): string;
}
