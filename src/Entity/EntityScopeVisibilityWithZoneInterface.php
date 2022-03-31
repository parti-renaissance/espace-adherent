<?php

namespace App\Entity;

use App\Entity\Geo\Zone;

interface EntityScopeVisibilityWithZoneInterface extends EntityScopeVisibilityInterface
{
    public function getZone(): ?Zone;
}
