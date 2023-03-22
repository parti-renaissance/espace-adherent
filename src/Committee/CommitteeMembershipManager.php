<?php

namespace App\Committee;

use App\Address\AddressInterface;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Geo\ZoneMatcher;
use App\Repository\CommitteeRepository;

class CommitteeMembershipManager
{
    public function __construct(
        private readonly CommitteeManager $committeeManager,
        private readonly ZoneMatcher $zoneMatcher,
        private readonly CommitteeRepository $committeeRepository
    ) {
    }

    public function findCommitteeByAddress(AddressInterface $address): ?Committee
    {
        if (!$zones = $this->zoneMatcher->match($address)) {
            return null;
        }

        foreach ($this->orderZones($zones, true, Zone::COMMITTEE_TYPES) as $zone) {
            if ($committee = current($this->committeeRepository->findInZones(zones: [$zone], withZoneParents: false))) {
                return $committee;
            }
        }

        return null;
    }

    public function followCommittee(
        Adherent $adherent,
        Committee $committee,
        CommitteeMembershipTriggerEnum $trigger
    ): void {
        $alreadyFollow = false;

        // 1. Comes out of the existing committees
        foreach ($adherent->getMemberships()->getCommitteeV2Memberships() as $membership) {
            if (!$alreadyFollow = $membership->getCommittee()->equals($committee)) {
                $this->committeeManager->unfollowCommittee($adherent, $membership->getCommittee());
            }
        }

        // 2. Follow the new committee
        if (!$alreadyFollow) {
            $this->committeeManager->followCommittee($adherent, $committee, $trigger);
        }
    }

    /**
     * @return Zone[]
     */
    public function orderZones(array $zones, bool $flattenParents = false, array $types = []): array
    {
        $flattenZones = $flattenParents ? $this->zoneMatcher->flattenZones($zones, $types) : $zones;

        usort(
            $flattenZones,
            fn (Zone $a, Zone $b) => array_search($a->getType(), Zone::COMMITTEE_TYPES) <=> array_search($b->getType(), Zone::COMMITTEE_TYPES)
        );

        return $flattenZones;
    }
}
