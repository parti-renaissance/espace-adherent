<?php

namespace App\Geo;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\ReferentTag;

class ManagedZoneProvider
{
    public const DEPUTY = 'deputy';
    public const LRE = 'lre';
    public const REFERENT = 'referent';
    public const SENATOR = 'senator';
    public const SENATORIAL_CANDIDATE = 'senatorial_candidate';
    public const CANDIDATE = 'candidate';
    public const LEGISLATIVE_CANDIDATE = 'legislative_candidate';

    /**
     * @return Zone[]
     */
    public function getManagedZones(Adherent $adherent, string $spaceType): array
    {
        if (self::DEPUTY === $spaceType) {
            return [$adherent->getManagedDistrict()->getReferentTag()->getZone()];
        }

        if (self::LRE === $spaceType) {
            if ($adherent->getLreArea()->isAllTags()) {
                return [];
            }

            return [$adherent->getLreArea()->getReferentTag()->getZone()];
        }

        if (self::REFERENT === $spaceType) {
            return $adherent->getManagedArea()->getZones()->toArray();
        }

        if (self::SENATOR === $spaceType) {
            return [$adherent->getSenatorArea()->getDepartmentTag()->getZone()];
        }

        if (self::SENATORIAL_CANDIDATE === $spaceType) {
            $zones = [];

            /* @var ReferentTag $referentTag */
            $referentTags = $adherent->getSenatorialCandidateManagedArea()->getDepartmentTags();
            foreach ($referentTags as $referentTag) {
                $zones[] = $referentTag->getZone();
            }

            return $zones;
        }

        if (self::CANDIDATE === $spaceType) {
            return [$adherent->getCandidateManagedArea()->getZone()];
        }

        if (self::LEGISLATIVE_CANDIDATE === $spaceType) {
            return [$adherent->getLegislativeCandidateManagedDistrict()->getReferentTag()->getZone()];
        }

        throw new \InvalidArgumentException(sprintf('Invalid "%s" space type', $spaceType));
    }

    public function getManagedZonesIds(Adherent $adherent, string $spaceType): array
    {
        return array_map(
            static function (Zone $zone) { return $zone->getId(); },
            $this->getManagedZones($adherent, $spaceType)
        );
    }

    public function zoneBelongsToSome(Zone $zone, array $managedIds): bool
    {
        $ids = array_map(static function (Zone $zone): int {
            return $zone->getId();
        }, $zone->getParents());

        $ids[] = $zone->getId();

        $intersect = array_intersect($ids, $managedIds);

        return \count($intersect) > 0;
    }
}
