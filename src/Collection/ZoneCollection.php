<?php

declare(strict_types=1);

namespace App\Collection;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\ArrayCollection;

class ZoneCollection extends ArrayCollection
{
    /**
     * @return Zone[]
     */
    public function getParentsOfType(string $type): array
    {
        $zones = [];

        foreach ($this->toArray() as $zone) {
            $zones[] = $zone->getParentsOfType($type);
        }

        return array_unique(array_merge(...$zones));
    }
}
