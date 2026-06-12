<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\CandidateSelection;

use App\AdherentMessage\PublicationZone;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\JeMengage\Timeline\Indexer\UserProfileFactory;

/**
 * Builds the AudienceContext from the authenticated Adherent. Composes UserProfileFactory (single
 * source of the shared Adherent accessors) and derives the matcher-only dimensions with the same
 * semantics as the Algolia clause builder (GetTimelineFeedsController).
 */
class AudienceContextFactory
{
    public function __construct(private readonly UserProfileFactory $profileFactory)
    {
    }

    public function create(Adherent $user): AudienceContext
    {
        return new AudienceContext(
            $this->profileFactory->create($user),
            $this->expandTagPrefixes($user->tags ?? []),
            $this->reachZones($user),
            $this->zoneCodesByType($user),
        );
    }

    /**
     * Progressive prefixes of every tag ("a:b:c" -> "a", "a:b", "a:b:c"), the same expansion the
     * Algolia clause builder applies to both include and exclude tag conditions.
     *
     * @param string[] $tags
     *
     * @return string[]
     */
    private function expandTagPrefixes(array $tags): array
    {
        $prefixes = [];

        foreach ($tags as $tag) {
            $prefix = '';
            foreach (explode(':', $tag) as $i => $part) {
                $prefix = 0 === $i ? $part : $prefix.':'.$part;
                $prefixes[] = $prefix;
            }
        }

        return array_values(array_unique($prefixes));
    }

    /**
     * Reach zones of the base clause: assembly zone + DIRECT city zones only (Algolia parity — NOT
     * deep zones), in the "type:code" canonical shape the mirror stores in audience include.zones.
     *
     * @return string[]
     */
    private function reachZones(Adherent $user): array
    {
        $zones = [];

        $assemblyZone = $user->getAssemblyZone();
        foreach ([$assemblyZone, ...$user->getZonesOfType(Zone::CITY)] as $zone) {
            if ($zone && ($type = $zone->getType()) && ($code = $zone->getCode())) {
                $zones[] = $type.':'.$code;
            }
        }

        return array_values(array_unique($zones));
    }

    /**
     * Deep zone codes grouped by the 9 publication zone types (same grouping as the Algolia clause
     * builder); types absent from the user zones keep an empty list.
     *
     * @return array<string, string[]>
     */
    private function zoneCodesByType(Adherent $user): array
    {
        $byType = array_fill_keys(PublicationZone::ZONE_TYPES, []);

        foreach ($user->getDeepZones() as $zone) {
            $type = $zone->getType();
            $code = $zone->getCode();
            if (isset($byType[$type]) && $code) {
                $byType[$type][] = $code;
            }
        }

        foreach ($byType as $type => $codes) {
            $byType[$type] = array_values(array_unique($codes));
        }

        return $byType;
    }
}
