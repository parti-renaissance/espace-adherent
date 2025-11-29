<?php

declare(strict_types=1);

namespace App\Entity;

interface InjectScopeZonesInterface extends ZoneableEntityInterface
{
    public function setZones(array $zones): void;
}
