<?php

namespace App\Geo;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\ReferentTag;

class ManagedZoneProvider
{
    /**
     * @return Zone[]
     */
    public function getManagedZones(Adherent $adherent, string $spaceType): array
    {
        if (AdherentSpaceEnum::DEPUTY === $spaceType) {
            return $adherent->getManagedDistrict() ? [$adherent->getManagedDistrict()->getReferentTag()->getZone()] : [];
        }

        if (AdherentSpaceEnum::LRE === $spaceType) {
            if (!$adherent->getLreArea()) {
                return [];
            }

            if ($adherent->getLreArea()->isAllTags()) {
                return [];
            }

            return [$adherent->getLreArea()->getReferentTag()->getZone()];
        }

        if (AdherentSpaceEnum::REFERENT === $spaceType) {
            return $adherent->getManagedArea() ? $adherent->getManagedArea()->getZones()->toArray() : [];
        }

        if (AdherentSpaceEnum::CORRESPONDENT === $spaceType) {
            return [$adherent->getCorrespondentZone()];
        }

        if (AdherentSpaceEnum::SENATOR === $spaceType) {
            return $adherent->getSenatorArea() ? [$adherent->getSenatorArea()->getDepartmentTag()->getZone()] : [];
        }

        if (AdherentSpaceEnum::SENATORIAL_CANDIDATE === $spaceType) {
            if (!$adherent->getSenatorialCandidateManagedArea()) {
                return [];
            }

            $zones = [];

            /* @var ReferentTag $referentTag */
            $referentTags = $adherent->getSenatorialCandidateManagedArea()->getDepartmentTags();
            foreach ($referentTags as $referentTag) {
                $zones[] = $referentTag->getZone();
            }

            return $zones;
        }

        if (\in_array($spaceType, [AdherentSpaceEnum::CANDIDATE, AdherentSpaceEnum::CANDIDATE_JECOUTE], true)) {
            return $adherent->getCandidateManagedArea() ? [$adherent->getCandidateManagedArea()->getZone()] : [];
        }

        if (AdherentSpaceEnum::LEGISLATIVE_CANDIDATE === $spaceType) {
            return $adherent->getLegislativeCandidateManagedDistrict() ? [$adherent->getLegislativeCandidateManagedDistrict()->getReferentTag()->getZone()] : [];
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

    public function isManagerOfZone(Adherent $adherent, string $spaceType, Zone $zone): bool
    {
        return $this->zoneBelongsToSome($zone, $this->getManagedZonesIds($adherent, $spaceType));
    }
}
