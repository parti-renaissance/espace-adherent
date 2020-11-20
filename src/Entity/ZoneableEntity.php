<?php

namespace App\Entity;

use App\Entity\Geo\Zone;

interface ZoneableEntity extends EntityPostAddressInterface
{
    public function addZone(Zone $Zone): void;

    public function removeZone(Zone $Zone): void;

    public function clearZones(): void;
}
