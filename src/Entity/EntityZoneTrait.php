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
     * @ORM\ManyToMany(targetEntity="App\Entity\Geo\Zone")
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
}
