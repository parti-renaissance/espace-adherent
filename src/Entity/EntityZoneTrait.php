<?php

namespace App\Entity;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait EntityZoneTrait
{
    /**
     * @var Collection|Zone[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone", cascade={"persist"})
     */
    protected $zones;

    /**
     * @return Collection|Zone[]
     */
    public function getZones(): Collection
    {
        return $this->zones;
    }

    public function addZone(Zone $Zone): void
    {
        if (!$this->zones->contains($Zone)) {
            $this->zones->add($Zone);
        }
    }

    public function removeZone(Zone $Zone): void
    {
        $this->zones->remove($Zone);
    }

    public function clearZones(): void
    {
        $this->zones->clear();
    }

    public function getZonesCodes(): array
    {
        return array_map(function (Zone $zone) {
            return $zone->getCode();
        }, $this->zones->toArray());
    }

    public function hasZoneOutsideFrance(): bool
    {
        /** @var Zone $zone */
        foreach ($this->zones as $zone) {
            if (!$zone->isInFrance()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Zone[]
     */
    public function getZonesOfType(string $type): array
    {
        return array_filter($this->zones->toArray(), function (Zone $zone) use ($type) {
            return $type === $zone->getType();
        });
    }

    /**
     * @return Zone[]
     */
    public function getParentZonesOfType(string $type): array
    {
        $zones = [];
        foreach ($this->zones as $zone) {
            $zones = array_merge($zones, $zone->getParentsOfType($type));
        }

        return array_unique($zones);
    }
}
