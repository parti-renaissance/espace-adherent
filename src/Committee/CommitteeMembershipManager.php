<?php

namespace App\Committee;

use App\Address\AddressInterface;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Geo\Zone;
use App\Geo\ZoneMatcher;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\CommitteeRepository;
use Doctrine\ORM\EntityManagerInterface;

class CommitteeMembershipManager
{
    public function __construct(
        private readonly CommitteeManager $committeeManager,
        private readonly ZoneMatcher $zoneMatcher,
        private readonly CommitteeRepository $committeeRepository,
        private readonly CommitteeMembershipRepository $committeeMembershipRepository,
        private readonly EntityManagerInterface $entityManager,
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

    public function batchFollowCommittee(
        array $adherents,
        Committee $committee,
        CommitteeMembershipTriggerEnum $trigger,
    ): void {
        // 1. Comes out of the existing committees
        $this->committeeMembershipRepository->unfollowAllCommittees(array_map(fn (Adherent $a) => $a->getId(), $adherents), $committee);

        // 2. Follow the new committee
        foreach ($adherents as $adherent) {
            if ($this->committeeMembershipRepository->findMembership($adherent, $committee)) {
                continue;
            }
            $this->entityManager->persist(CommitteeMembership::createFollower($committee, $adherent, $trigger));
        }
        $this->entityManager->flush();
    }

    public function followCommittee(
        Adherent $adherent,
        Committee $committee,
        CommitteeMembershipTriggerEnum $trigger,
    ): void {
        $alreadyFollow = false;

        // 1. Comes out of the existing committees
        foreach ($this->committeeMembershipRepository->findMemberships($adherent) as $membership) {
            if (!$membership->isCommitteeV2()) {
                continue;
            }

            if ($membership->getCommittee()->getId() !== $committee->getId()) {
                $this->committeeManager->unfollowCommittee($adherent, $membership->getCommittee());
            } elseif (!$alreadyFollow) {
                $alreadyFollow = true;
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

    /**
     * @return CommitteeMembership[]
     */
    public function getCommitteeMemberships(Committee $committee): array
    {
        return $this->committeeMembershipRepository->findCommitteeMemberships($committee)->toArray();
    }

    public function unfollowCommittee(CommitteeMembership $membership, Committee $committee): void
    {
        $this->committeeManager->unfollowCommittee($membership->getAdherent(), $committee);
    }
}
