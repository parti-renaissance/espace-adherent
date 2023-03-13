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

        foreach ($this->getOrderedZones($zones) as $zone) {
            if ($committee = current($this->committeeRepository->findInZones(zones: [$zone], withZoneParents: false))) {
                return $committee;
            }
        }

        return null;
    }

    public function followCommittee(Adherent $adherent, Committee $committee, string $trigger): void
    {
        // 1. Comes out of the existing committees
        foreach ($adherent->getMemberships()->getCommitteeV2Memberships() as $membership) {
            if (!$membership->getCommittee()->equals($committee)) {
                $this->committeeManager->unfollowCommittee($adherent, $membership->getCommittee());
            }
        }

        // 2. Follow the new committee
        $this->committeeManager->followCommittee($adherent, $committee, $trigger);
    }

    /**
     * @return Zone[]
     */
    private function getOrderedZones(array $zones): array
    {
        $flattenZones = $this->zoneMatcher->flattenZones($zones, Zone::COMMITTEE_TYPES);

        usort(
            $flattenZones,
            fn (Zone $a, Zone $b) => array_search($a->getType(), Zone::COMMITTEE_TYPES) <=> array_search($b->getType(), Zone::COMMITTEE_TYPES)
        );

        return $flattenZones;
    }
}
