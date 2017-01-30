<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CommitteeMembershipRepository;

class CommitteeManager
{
    private $adherentRepository;
    private $membershipRepository;

    public function __construct(
        AdherentRepository $adherentRepository,
        CommitteeMembershipRepository $membershipRepository
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->membershipRepository = $membershipRepository;
    }

    public function findCommitteeHostsList(Committee $committee): array
    {
        $uuids = array_map(
            function (CommitteeMembership $membership) {
                return (string) $membership->getAdherentUuid();
            },
            $this->membershipRepository->findHostMemberships((string) $committee->getUuid())
        );

        return $this->adherentRepository->findList($uuids);
    }
}
