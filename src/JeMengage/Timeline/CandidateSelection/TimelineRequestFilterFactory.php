<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\CandidateSelection;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

/**
 * Resolves the timeline view-filter query params (zone/committee/instance) into ANDed conditions —
 * the exact semantics the Algolia clause builder applied inline, including the quirk: a `zone`
 * param whose uuid is invalid or unknown degrades to a NATIONAL condition, not to "no filter".
 *
 * Hardened over the legacy inline code: getZonesOfType() is an array_filter with PRESERVED KEYS, so
 * indexing [0] could miss an existing zone (warning + silently dropped filter) — reset-style access
 * fixes it.
 */
class TimelineRequestFilterFactory
{
    public function __construct(private readonly ZoneRepository $zoneRepository)
    {
    }

    public function createFromRequest(Request $request, Adherent $user): ?TimelineRequestFilter
    {
        $conditions = [];

        if ($zoneParam = $request->query->get('zone')) {
            $condition = new RequestFilterCondition(RequestFilterCondition::NATIONAL);
            if (Uuid::isValid($zoneParam) && ($zone = $this->zoneRepository->findOneByUuid($zoneParam))) {
                $condition = $this->zoneCondition([$zone]) ?? $condition;
            }
            $conditions[] = $condition;
        }

        if (($committee = $request->query->get('committee')) && Uuid::isValid($committee)) {
            $conditions[] = new RequestFilterCondition(RequestFilterCondition::COMMITTEE, $committee);
        }

        if (($instance = $request->query->get('instance')) && ($condition = $this->instanceCondition($instance, $user))) {
            $conditions[] = $condition;
        }

        return [] !== $conditions ? new TimelineRequestFilter($conditions) : null;
    }

    private function instanceCondition(string $instance, Adherent $user): ?RequestFilterCondition
    {
        return match ($instance) {
            'committee' => ($uuid = $user->getCommitteeMembership()?->getCommitteeUuid())
                ? new RequestFilterCondition(RequestFilterCondition::COMMITTEE, (string) $uuid)
                : null,
            'circonscription' => $this->zoneCondition($user->isForeignResident()
                ? $user->getZonesOfType(Zone::FOREIGN_DISTRICT)
                : $user->getZonesOfType(Zone::DISTRICT)),
            'assembly' => $this->zoneCondition(array_filter([$user->getAssemblyZone()])),
            'agora' => ($agora = ($user->agoraMemberships->first() ?: null)?->agora)
                ? new RequestFilterCondition(RequestFilterCondition::AGORA, $agora->getUuid()->toRfc4122())
                : null,
            default => null,
        };
    }

    /**
     * @param Zone[] $zones possibly with preserved (non-contiguous) keys
     */
    private function zoneCondition(array $zones): ?RequestFilterCondition
    {
        $zone = array_values($zones)[0] ?? null;
        if (!$zone || !($type = $zone->getType()) || !($code = $zone->getCode())) {
            return null;
        }

        return new RequestFilterCondition(RequestFilterCondition::ZONE, $type.':'.$code);
    }
}
