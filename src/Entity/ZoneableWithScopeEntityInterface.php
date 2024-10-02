<?php

namespace App\Entity;

interface ZoneableWithScopeEntityInterface extends ZoneableEntityInterface
{
    public function getScope(): ?string;
}
