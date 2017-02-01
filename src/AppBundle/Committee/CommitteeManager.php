<?php

namespace AppBundle\Committee;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Committee;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CommitteeMembershipRepository;

class CommitteeManager
{
    const EXCLUDE_HOSTS = false;
    const INCLUDE_HOSTS = true;

    private $adherentRepository;
    private $membershipRepository;

    public function __construct(
        AdherentRepository $adherentRepository,
        CommitteeMembershipRepository $membershipRepository
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->membershipRepository = $membershipRepository;
    }

    public function getMembersCount(Committee $committee): int
    {
        return $this->membershipRepository->countMembers($committee->getUuid()->toString());
    }

    public function findCommitteeHostsList(Committee $committee): AdherentCollection
    {
        $uuids = $this
            ->membershipRepository
            ->findHostMemberships((string) $committee->getUuid())
            ->getAdherentUuids();

        return $this->findAdherentsList($uuids);
    }

    public function findCommitteeFollowersList(Committee $committee, bool $withHosts = self::INCLUDE_HOSTS): AdherentCollection
    {
        $uuids = $this
            ->membershipRepository
            ->findFollowerMemberships((string) $committee->getUuid(), $withHosts)
            ->getAdherentUuids();

        return $this->findAdherentsList($uuids);
    }

    public function findOptinCommitteeFollowersList(Committee $committee, bool $withHosts = self::INCLUDE_HOSTS): AdherentCollection
    {
        return $this->findCommitteeFollowersList($committee, $withHosts)->getCommitteesNotificationsSubscribers();
    }

    private function findAdherentsList(array $uuids): AdherentCollection
    {
        return $this->adherentRepository->findList($uuids);
    }
}
