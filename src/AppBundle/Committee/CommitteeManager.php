<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Geocoder\Coordinates;
use Doctrine\Common\Persistence\ManagerRegistry;

class CommitteeManager
{
    const EXCLUDE_HOSTS = false;
    const INCLUDE_HOSTS = true;

    private const COMMITTEE_PROPOSALS_COUNT = 3;

    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getMembersCount(Committee $committee): int
    {
        return $this->registry->getRepository(CommitteeMembership::class)->countMembers($committee->getUuid()->toString());
    }

    public function getCommitteeHosts(Committee $committee): AdherentCollection
    {
        return $this->registry
            ->getRepository(CommitteeMembership::class)
            ->findHostMembers($committee->getUuid()->toString());
    }

    public function getCommitteeFollowers(Committee $committee, bool $withHosts = self::INCLUDE_HOSTS): AdherentCollection
    {
        return $this->registry
            ->getRepository(CommitteeMembership::class)
            ->findFollowers($committee->getUuid()->toString(), $withHosts);
    }

    public function getOptinCommitteeFollowers(Committee $committee, bool $withHosts = self::INCLUDE_HOSTS): AdherentCollection
    {
        return $this->getCommitteeFollowers($committee, $withHosts)->getCommitteesNotificationsSubscribers();
    }

    public function getNearbyCommittees(Coordinates $coordinates, $limit = self::COMMITTEE_PROPOSALS_COUNT)
    {
        $data = [];
        $committeeMembershipRepository = $this->registry->getRepository(CommitteeMembership::class);
        $committees = $this->registry->getRepository(Committee::class)->findNearbyCommittees($limit, $coordinates);

        foreach ($committees as $committee) {
            $uuid = $committee->getUuid()->toString();

            $data[$uuid] = [
                'committee' => $committee,
                'memberships_count' => $committeeMembershipRepository->countMembers($uuid),
            ];
        }

        return $data;
    }

    /**
     * @param Adherent $adherent   The follower
     * @param string[] $committees An array of committee uuids
     */
    public function followCommittees(Adherent $adherent, array $committees)
    {
        if (empty($committees)) {
            return;
        }

        $manager = $this->registry->getManager();

        foreach ($this->registry->getRepository(Committee::class)->findByUuid($committees) as $committee) {
            $manager->persist($adherent->followCommittee($committee));
        }

        $manager->flush();
    }
}
