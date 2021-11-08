<?php

namespace App\Entity;

use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait EntityZoneTrait
{
    /**
     * @var Collection|Zone[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone", cascade={"persist"})
     *
     * @Groups({"phoning_campaign_read", "phoning_campaign_write"})
     */
    protected $zones;

    /**
     * @return Collection|Zone[]
     */
    public function getZones(): Collection
    {
        return $this->zones;
    }

    public function setZones(array $zones): void
    {
        array_walk($zones, [$this, 'addZone']);
    }

    public function addZone(Zone $Zone): void
    {
        if (!$this->zones->contains($Zone)) {
            $this->zones->add($Zone);
        }
    }

    public function removeZone(Zone $zone): void
    {
        $this->zones->removeElement($zone);
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
    public function getZonesOfType(string $type, bool $deep = false): array
    {
        return array_filter(
            $deep ? array_merge($this->zones->toArray(), $this->getParentZones()) : $this->zones->toArray(),
            function (Zone $zone) use ($type) {
                return $type === $zone->getType();
            }
        );
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

    public function getParentZones(): array
    {
        return array_merge(...array_map(function (Zone $zone) {
            return $zone->getParents();
        }, $this->zones->toArray()));
    }
}
