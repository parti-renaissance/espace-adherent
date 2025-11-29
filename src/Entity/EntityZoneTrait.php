<?php

declare(strict_types=1);

namespace App\Entity;

use App\Collection\ZoneCollection;
use App\Entity\Geo\Zone;
use App\Validator\ZoneInScopeZones as AssertZoneInScopeZones;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityZoneTrait
{
    /**
     * @var ZoneCollection|Zone[]
     */
    #[Assert\All(
        constraints: [new AssertZoneInScopeZones()],
        groups: ['zone_based_role_write'],
    )]
    #[Groups([
        'phoning_campaign_read',
        'phoning_campaign_write',
        'managed_users_list',
        'managed_user_read',
        'zone_based_role_read',
        'zone_based_role_write',
        'profile_update',
    ])]
    #[ORM\ManyToMany(targetEntity: Zone::class, cascade: ['persist'])]
    protected Collection $zones;

    /**
     * @return ZoneCollection|Zone[]
     */
    public function getZones(): ZoneCollection
    {
        if (!$this->zones instanceof ZoneCollection) {
            return new ZoneCollection($this->zones->toArray());
        }

        return $this->zones;
    }

    public function setZones(array $zones): void
    {
        $this->zones->clear();
        $this->zones = new ZoneCollection($zones);
    }

    public function addZone(Zone $zone): void
    {
        if (!$this->zones->contains($zone)) {
            $this->zones->add($zone);
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

    public static function getZonesPropertyName(): string
    {
        return 'zones';
    }

    public function getZonesCodes(): array
    {
        return array_map(function (Zone $zone) {
            return $zone->getCode();
        }, $this->zones->toArray());
    }

    /**
     * @return Zone[]
     */
    public function getZonesOfType(string $type, bool $deep = false): array
    {
        return array_filter(
            $deep ? $this->getDeepZones() : $this->zones->toArray(),
            function (Zone $zone) use ($type) {
                return $type === $zone->getType();
            }
        );
    }

    /**
     * Return DPT zone or FDE zone if adherent is outside France
     */
    public function getAssemblyZone(): ?Zone
    {
        foreach ($this->getDeepZones() as $zone) {
            if ($zone->isAssemblyZone()) {
                return $zone;
            }
        }

        return null;
    }

    /**
     * @return Zone[]
     */
    public function getDeepZones(): array
    {
        return array_merge($this->zones->toArray(), $this->getParentZones());
    }

    /**
     * @return Zone[]
     */
    public function getParentZonesOfType(string $type): array
    {
        $zones = [];
        foreach ($this->zones as $key => $zone) {
            /** @var Zone $zone */
            // Move parents of District|Canton zone to the end of collection
            // as District parents can be wrong related to non-detailed polygons
            $newKey = \in_array($zone->getType(), [Zone::DISTRICT, Zone::CANTON], true) ? (1000000 + (int) $zone->isDistrict()) : $key;
            $zones[$newKey] = $zone->getParentsOfType($type);
        }

        sort($zones);

        return array_unique(array_merge(...$zones));
    }

    public function getParentZones(): array
    {
        return array_unique(array_merge(...array_map(function (Zone $zone) {
            return $zone->getParents();
        }, $this->zones->toArray())));
    }

    public function getParisBoroughOrDepartment(): ?Zone
    {
        $parisBoroughs = array_filter($this->zones->toArray(), function (Zone $zone) {
            return Zone::BOROUGH === $zone->getType() && str_starts_with($zone->getCode(), '75');
        });

        if ($parisBorough = 1 === \count($parisBoroughs) ? current($parisBoroughs) : null) {
            return $parisBorough;
        }

        $departments = $this->getParentZonesOfType(Zone::DEPARTMENT);

        return \count($departments) ? current($departments) : null;
    }

    public static function alterQueryBuilderForZones(QueryBuilder $queryBuilder, string $rootAlias): void
    {
    }
}
