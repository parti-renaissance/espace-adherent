<?php

namespace App\Entity;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\Collection;

interface EntityScopeVisibilityWithZonesInterface extends EntityScopeVisibilityInterface
{
    /**
     * @return Collection|Zone[]
     */
    public function getZones(): Collection;
}
