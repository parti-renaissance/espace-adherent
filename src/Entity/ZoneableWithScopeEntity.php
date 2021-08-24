<?php

namespace App\Entity;

interface ZoneableWithScopeEntity extends ZoneableEntity
{
    public function getScope(): ?string;
}
