<?php

declare(strict_types=1);

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Scope\Scope;

interface ScopeGeneratorInterface
{
    public const DELEGATED_SCOPE_PREFIX = 'delegated_';

    public function generate(Adherent $adherent): Scope;

    public function supports(Adherent $adherent): bool;

    public function getCode(): string;

    public function setDelegatedAccess(DelegatedAccess $delegatedAccess): void;

    public function getDelegatedAccess(): ?DelegatedAccess;

    public function isDelegatedAccess(): bool;
}
