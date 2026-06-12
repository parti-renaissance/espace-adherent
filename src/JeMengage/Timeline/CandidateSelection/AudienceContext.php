<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\CandidateSelection;

use App\JeMengage\Timeline\Indexer\UserProfile;

/**
 * Resolved user targeting dimensions for the local audience matching (DESIGN § Spec de matching).
 * Wraps the ranker UserProfile (single source of the Adherent accessors) and adds the matcher-only
 * derivations the profile does not carry.
 */
class AudienceContext
{
    /**
     * @param string[]                $tagPrefixes     progressive prefixes of the user tags ("a", "a:b", "a:b:c")
     * @param string[]                $reachZones      "type:code" — assembly zone + DIRECT city zones only (Algolia base clause parity)
     * @param array<string, string[]> $zoneCodesByType deep zone codes grouped by the 9 PublicationZone::ZONE_TYPES
     */
    public function __construct(
        public readonly UserProfile $profile,
        public readonly array $tagPrefixes,
        public readonly array $reachZones,
        public readonly array $zoneCodesByType,
    ) {
    }
}
