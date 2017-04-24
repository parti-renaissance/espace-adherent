<?php

namespace AppBundle\Committee;

use AppBundle\Collection\CommitteeMembershipCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Exception\CommitteeMembershipException;
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

    public function isPromotableHost(Adherent $adherent, Committee $committee): bool
    {
        if (!$membership = $this->getMembershipRepository()->findMembership($adherent, $committee->getUuid())) {
            return false;
        }

        return $membership->isFollower();
    }

    public function isDemotableHost(Adherent $adherent, Committee $committee): bool
    {
        if (!$membership = $this->getMembershipRepository()->findMembership($adherent, $committee->getUuid())) {
            return false;
        }

        return $membership->isHostMember();
    }

    public function isCommitteeHost(Adherent $adherent): bool
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related committee memberships entities.
        if ($adherent->isHost()) {
            return true;
        }

        return $this->getMembershipRepository()->hostCommittee($adherent);
    }

    public function hostCommittee(Adherent $adherent, Committee $committee): bool
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related committee memberships entities.
        if ($adherent->isHostOf($committee)) {
            return true;
        }

        return $this->getMembershipRepository()->hostCommittee($adherent, $committee->getUuid());
    }

    public function superviseCommittee(Adherent $adherent, Committee $committee): bool
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related committee memberships entities.
        if ($adherent->isSupervisorOf($committee)) {
            return true;
        }

        return $this->getMembershipRepository()->superviseCommittee($adherent, $committee->getUuid());
    }

    public function getAdherentCommittees(Adherent $adherent): array
    {
        // Prevent SQL query if the adherent doesn't follow any committees yet.
        if (!count($memberships = $adherent->getMemberships())) {
            return [];
        }

        $committees = $this
            ->getCommitteeRepository()
            ->findCommittees($memberships->getCommitteeUuids(), CommitteeRepository::INCLUDE_UNAPPROVED)
            ->getOrderedCommittees($adherent->getMemberships())
            ->filter(function (Committee $committee) use ($adherent) {
                // Any approved committee is kept.
                if ($committee->isApproved()) {
                    return $committee;
                }

                // However, an unapproved committee is kept only if it was created by the adherent.
                if ($committee->isCreatedBy($adherent->getUuid())) {
                    return $committee;
                }
            })
        ;

        return $committees->toArray();
    }

    public function getTimeline(Committee $committee, int $limit = 30, int $firstResultIndex = 0): Paginator
    {
        return $this
            ->getCommitteeFeedItemRepository()
            ->findPaginatedMostRecentFeedItems((string) $committee->getUuid(), $limit, $firstResultIndex)
        ;
    }

    public function countCommitteeHosts(Committee $committee): int
    {
        return $this->getMembershipRepository()->countHostMembers($committee->getUuid());
    }

    public function countCommitteeSupervisors(Committee $committee): int
    {
        return $this->getMembershipRepository()->countSupervisorMembers($committee->getUuid());
    }

    public function getCommitteeHosts(Committee $committee): AdherentCollection
    {
        return $this->getMembershipRepository()->findHostMembers($committee->getUuid());
    }

    public function getCommitteeCreator(Committee $committee): Adherent
    {
        return $this->getCommitteeHosts($committee)->first();
    }

    public function getCommitteeFollowers(Committee $committee, bool $withHosts = self::INCLUDE_HOSTS): AdherentCollection
    {
        return $this->getMembershipRepository()->findFollowers($committee->getUuid(), $withHosts);
    }

    public function getOptinCommitteeFollowers(Committee $committee): AdherentCollection
    {
        $followers = $this->getCommitteeFollowers($committee, self::EXCLUDE_HOSTS);

        return $this
            ->getCommitteeHosts($committee)
            ->merge($followers->getCommitteesNotificationsSubscribers())
        ;
    }

    /**
     * Returns the list of committees that are located near a point of origin.
     *
     * @param Coordinates $coordinates
     * @param int         $limit
     *
     * @return Committee[]
     */
    public function getNearbyCommittees(Coordinates $coordinates, $limit = self::COMMITTEE_PROPOSALS_COUNT): array
    {
        $data = [];
        $committees = $this->getCommitteeRepository()->findNearbyCommittees($limit, $coordinates);

        foreach ($committees as $committee) {
            $data[(string) $committee->getUuid()] = $committee;
        }

        return $data;
    }

    public function getCommitteeMembers(Committee $committee): AdherentCollection
    {
        return $this->getMembershipRepository()->findMembers($committee->getUuid());
    }

    public function getCommitteeMemberships(Committee $committee): CommitteeMembershipCollection
    {
        return $this->getMembershipRepository()->findCommitteeMemberships($committee->getUuid());
    }

    public function getCommitteeMembership(Adherent $adherent, Committee $committee): ?CommitteeMembership
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related committee memberships entities.
        if ($membership = $adherent->getMembershipFor($committee)) {
            return $membership;
        }

        return $this->getMembershipRepository()->findMembership($adherent, $committee->getUuid());
    }

    /**
     * Promotes an adherent to be a host of a committee.
     *
     * @param Adherent  $adherent
     * @param Committee $committee
     * @param bool      $flush
     */
    public function promote(Adherent $adherent, Committee $committee, bool $flush = true): void
    {
        $membership = $this->getMembershipRepository()->findMembership($adherent, $committee->getUuid());
        $membership->promote();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Promotes an adherent to be a host of a committee.
     *
     * @param Adherent  $adherent
     * @param Committee $committee
     * @param bool      $flush
     */
    public function demote(Adherent $adherent, Committee $committee, bool $flush = true): void
    {
        $membership = $this->getMembershipRepository()->findMembership($adherent, $committee->getUuid());
        $membership->demote();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Approves one committee.
     *
     * @param Committee $committee
     * @param bool      $flush
     */
    public function approveCommittee(Committee $committee, bool $flush = true): void
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
    public function refuseCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->refused();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    public function isFollowingCommittee(Adherent $adherent, Committee $committee): bool
    {
        return $this->getCommitteeMembership($adherent, $committee) instanceof CommitteeMembership;
    }

    /**
     * Makes an adherent follow multiple committees at once.
     *
     * @param Adherent $adherent   The follower
     * @param string[] $committees An array of committee UUIDs
     */
    public function followCommittees(Adherent $adherent, array $committees): void
    {
        if (empty($committees)) {
            return;
        }

        foreach ($this->getCommitteeRepository()->findByUuid($committees) as $committee) {
            if (!$this->isFollowingCommittee($adherent, $committee)) {
                $this->followCommittee($adherent, $committee);
            }
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
    public function followCommittee(Adherent $adherent, Committee $committee, $flush = true): void
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
    public function unfollowCommittee(Adherent $adherent, Committee $committee, bool $flush = true): void
    {
        $membership = $this->getMembershipRepository()->findMembership($adherent, $committee->getUuid());

        if ($membership) {
            $this->doUnfollowCommittee($membership, $committee, $flush);
        }
    }

    private function doUnfollowCommittee(CommitteeMembership $membership, Committee $committee, bool $flush = true): void
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

    public function changePrivilege(Adherent $adherent, Committee $committee, string $privilege): void
    {
        CommitteeMembership::checkPrivilege($privilege);

        if (!$committeeMembership = $this->getCommitteeMembership($adherent, $committee)) {
            return;
        }

        // We can't have more than 1 supervisors per committee
        if (CommitteeMembership::COMMITTEE_SUPERVISOR === $privilege && $this->countCommitteeSupervisors($committee)) {
            throw CommitteeMembershipException::createNotPromotableSupervisorPrivilegeException($committeeMembership->getUuid());
        }

        $committeeMembership->setPrivilege($privilege);

        $this->getManager()->flush();
    }
}
