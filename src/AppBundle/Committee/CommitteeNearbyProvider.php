<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Committee;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;

class CommitteeNearbyProvider
{
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
     * @param int $count
     *
     * @return array
     */
    public function findNearbyCommittees(int $count): array
    {
        /** @var Committee[] $committees */
        $committees = $this->committee->findBy(['status' => Committee::APPROVED], ['id' => 'desc'], $count);

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
