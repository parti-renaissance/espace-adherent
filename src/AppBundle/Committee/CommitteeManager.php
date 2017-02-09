<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Repository\CommitteeFeedItemRepository;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

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

    public function getAdherentCommittees(Adherent $adherent, int $limit = 5): array
    {
        $memberships = $this->getMembershipRepository()->findMemberships($adherent);

        if (!$memberships->count()) {
            return [];
        }

        // We want the hosted committees first.
        $repository = $this->getCommitteeRepository();

        $hostedCommittees = $repository->findCommittees(
            $memberships->getCommitteeHostMemberships()->getCommitteeUuids(),
            CommitteeRepository::INCLUDE_UNAPPROVED,
            $limit
        );

        if ($limit === $hostedCommitteesCount = count($hostedCommittees)) {
            return $hostedCommittees;
        }

        // Then, the followed committees.
        $limit -= $hostedCommitteesCount;
        $followedCommittees = $repository->findCommittees(
            $memberships->getCommitteeFollowerMemberships()->getCommitteeUuids(),
            CommitteeRepository::ONLY_APPROVED,
            $limit
        );

        return $hostedCommittees + $followedCommittees;
    }

    public function getTimeline(Committee $committee, int $limit = 30, int $firstResultIndex = 0): Paginator
    {
        return $this
            ->getCommitteeFeedItemRepository()
            ->findPaginatedMostRecentFeedItems((string) $committee->getUuid(), $limit, $firstResultIndex)
        ;
    }

    /**
     * Returns the total number of members in the committee.
     *
     * @param Committee $committee The committee
     *
     * @return int
     */
    public function getMembersCount(Committee $committee): int
    {
        return $this->getMembershipRepository()->countMembers($committee->getUuid()->toString());
    }

    public function getCommitteeHosts(Committee $committee): AdherentCollection
    {
        return $this->getMembershipRepository()->findHostMembers($committee->getUuid()->toString());
    }

    public function getCommitteeCreator(Committee $committee): Adherent
    {
        return $this->getCommitteeHosts($committee)->first();
    }

    public function getCommitteeFollowers(Committee $committee, bool $withHosts = self::INCLUDE_HOSTS): AdherentCollection
    {
        return $this->getMembershipRepository()->findFollowers($committee->getUuid()->toString(), $withHosts);
    }

    public function getOptinCommitteeFollowers(Committee $committee, bool $withHosts = self::INCLUDE_HOSTS): AdherentCollection
    {
        return $this->getCommitteeFollowers($committee, $withHosts)->getCommitteesNotificationsSubscribers();
    }

    public function getNearbyCommittees(Coordinates $coordinates, $limit = self::COMMITTEE_PROPOSALS_COUNT)
    {
        $data = [];
        $committeeMembershipRepository = $this->getMembershipRepository();
        $committees = $this->getCommitteeRepository()->findNearbyCommittees($limit, $coordinates);

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
     * @param Committee $committee
     *
     * @return AdherentCollection
     */
    public function getCommitteeMembers(Committee $committee): AdherentCollection
    {
        return $this->getMembershipRepository()->findMembers($committee->getUuid());
    }

    /**
     * Approves one committee.
     *
     * @param Committee $committee
     * @param bool      $flush
     */
    public function approveCommittee(Committee $committee, bool $flush = true)
    {
        $committee->approved();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Refuses one committee.
     *
     * @param Committee $committee
     * @param bool      $flush
     */
    public function refuseCommittee(Committee $committee, bool $flush = true)
    {
        $committee->refused();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Makes an adherent follow multiple committees at once.
     *
     * @param Adherent $adherent   The follower
     * @param string[] $committees An array of committee UUIDs
     */
    public function followCommittees(Adherent $adherent, array $committees)
    {
        if (empty($committees)) {
            return;
        }

        foreach ($this->getCommitteeRepository()->findByUuid($committees) as $committee) {
            $this->followCommittee($adherent, $committee);
        }

        $this->getManager()->flush();
    }

    /**
     * Makes an adherent follow one committee.
     *
     * @param Adherent  $adherent  The follower
     * @param Committee $committee The committee to follow
     * @param bool      $flush     Whether or not to flush the transaction
     */
    public function followCommittee(Adherent $adherent, Committee $committee, $flush = true)
    {
        $manager = $this->getManager();
        $manager->persist($adherent->followCommittee($committee));

        if ($flush) {
            $manager->flush();
        }
    }

    /**
     * Makes an adherent unfollow one committee.
     *
     * @param Adherent  $adherent  The follower
     * @param Committee $committee The committee to follow
     * @param bool      $flush     Whether or not to flush the transaction
     */
    public function unfollowCommittee(Adherent $adherent, Committee $committee, bool $flush = true)
    {
        $membership = $this->getMembershipRepository()->findMembership($adherent, (string) $committee->getUuid());

        if ($membership) {
            $this->doUnfollowCommittee($membership, $committee, $flush);
        }
    }

    private function doUnfollowCommittee(CommitteeMembership $membership, Committee $committee, bool $flush = true)
    {
        $manager = $this->getManager();

        $manager->remove($membership);
        $committee->decrementMembersCount();

        if ($flush) {
            $manager->flush();
        }
    }

    private function getManager(): ObjectManager
    {
        return $this->registry->getManager();
    }

    private function getCommitteeRepository(): CommitteeRepository
    {
        return $this->registry->getRepository(Committee::class);
    }

    private function getCommitteeFeedItemRepository(): CommitteeFeedItemRepository
    {
        return $this->registry->getRepository(CommitteeFeedItem::class);
    }

    private function getMembershipRepository(): CommitteeMembershipRepository
    {
        return $this->registry->getRepository(CommitteeMembership::class);
    }

    public function countApprovedCommittees(): int
    {
        return $this->getCommitteeRepository()->countApprovedCommittees();
    }
}
