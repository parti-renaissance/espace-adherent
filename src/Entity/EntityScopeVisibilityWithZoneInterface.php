<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Geo\Zone;

interface EntityScopeVisibilityWithZoneInterface extends EntityScopeVisibilityInterface
{
    public function getZone(): ?Zone;
}
