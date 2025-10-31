<?php

namespace App\Geo;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;

class ManagedZoneProvider
{
    public const PUBLIC_SPACE = 'public';

    /**
     * @return Zone[]
     */
    public function getManagedZones(Adherent $adherent, string $spaceType): array
    {
        if (self::PUBLIC_SPACE === $spaceType) {
            return [];
        }

        if (AdherentSpaceEnum::DEPUTY === $spaceType) {
            return [$adherent->getDeputyZone()];
        }

        if (AdherentSpaceEnum::CORRESPONDENT === $spaceType) {
            return [$adherent->getCorrespondentZone()];
        }

        if (\in_array($spaceType, [AdherentSpaceEnum::CANDIDATE, AdherentSpaceEnum::CANDIDATE_JECOUTE], true)) {
            return $adherent->getCandidateManagedArea() ? [$adherent->getCandidateManagedArea()->getZone()] : [];
        }

        if (AdherentSpaceEnum::LEGISLATIVE_CANDIDATE === $spaceType) {
            return [$adherent->getLegislativeCandidateZone()];
        }

        if (AdherentSpaceEnum::REGIONAL_COORDINATOR === $spaceType) {
            return $adherent->getRegionalCoordinatorZone();
        }

        throw new \InvalidArgumentException(\sprintf('Invalid "%s" space type', $spaceType));
    }

    public function getManagedZonesIds(Adherent $adherent, string $spaceType): array
    {
        return array_map(
            static function (Zone $zone) { return $zone->getId(); },
            $this->getManagedZones($adherent, $spaceType)
        );
    }

    public function zoneBelongsToSomeZones(Zone $zone, array $zones): bool
    {
        return $this->zoneBelongsToSome($zone, array_map(
            static function (Zone $zone) { return $zone->getId(); }, $zones)
        );
    }

    public function zoneBelongsToSome(Zone $zone, array $managedIds): bool
    {
        $intersect = array_intersect(
            array_map(static fn (Zone $zone) => $zone->getId(), $zone->getWithParents()),
            $managedIds
        );

        return \count($intersect) > 0;
    }
}
