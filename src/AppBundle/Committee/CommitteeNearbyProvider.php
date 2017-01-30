<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Committee;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;

class CommitteeNearbyProvider
{
    const COMMITTEE_PROPOSALS_COUNT = 3;

    private $committee;
    private $membership;

    /**
     * @param CommitteeRepository           $committee
     * @param CommitteeMembershipRepository $membership
     */
    public function __construct(CommitteeRepository $committee, CommitteeMembershipRepository $membership)
    {
        $this->committee = $committee;
        $this->membership = $membership;
    }

    /**
     * @param Coordinates $coordinates
     *
     * @return array
     */
    public function findNearbyCommittees(Coordinates $coordinates): array
    {
        /** @var Committee[] $committees */
        $committees = $this->committee->findNearbyCommittees(self::COMMITTEE_PROPOSALS_COUNT, $coordinates);

        foreach ($committees as $committee) {
            $uuid = (string) $committee->getUuid();

            $data[$uuid] = [
                'committee' => $committee,
                'memberships' => $this->membership->countMembers($uuid),
            ];
        }

        return $data ?? [];
    }
}
