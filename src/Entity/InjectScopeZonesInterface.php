<?php

namespace App\Entity;

interface InjectScopeZonesInterface extends ZoneableEntityInterface
{
    public function setZones(array $zones): void;
}
