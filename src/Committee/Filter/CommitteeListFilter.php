<?php

declare(strict_types=1);

namespace App\Committee\Filter;

use App\Entity\Geo\Zone;

class CommitteeListFilter
{
    /**
     * @var Zone[]
     */
    private $managedZones;

    /**
     * @var Zone[]
     */
    private $zones = [];

    public function __construct(array $managedZones = [])
    {
        $this->managedZones = $managedZones;
    }

    /**
     * @return Zone[]
     */
    public function getManagedZones(): array
    {
        return $this->managedZones;
    }

    /**
     * @return Zone[]
     */
    public function getZones(): array
    {
        return $this->zones;
    }

    /**
     * @param Zone[] $zones
     */
    public function setZones(array $zones): void
    {
        $this->zones = $zones;
    }

    public function toArray(): array
    {
        return [
            'zones' => array_map(static function (Zone $zone): int {
                return $zone->getId();
            }, $this->zones),
        ];
    }
}
