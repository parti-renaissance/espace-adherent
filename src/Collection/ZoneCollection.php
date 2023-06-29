<?php

namespace App\Collection;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\ArrayCollection;

class ZoneCollection extends ArrayCollection
{
    /**
     * @return Zone[]
     */
    public function getParentOfType(string $type): array
    {
        $zones = [];

        foreach ($this->toArray() as $zone) {
            /** @var Zone $zone */
            if ($zone->getType() === $type) {
                $zones[] = [$zone];

                continue;
            }

            $zones[] = $zone->getParentsOfType($type);
        }

        return array_unique(array_merge(...$zones));
    }
}
