<?php

declare(strict_types=1);

namespace App\Entity;

interface ZoneableWithScopeEntityInterface extends ZoneableEntityInterface
{
    public function getScope(): ?string;
}
