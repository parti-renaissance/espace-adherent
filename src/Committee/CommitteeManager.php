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
use AppBundle\Committee\Filter\CommitteeFilters;
use AppBundle\Repository\AdherentRepository;
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
        return $this->doGetAdherentCommittees($adherent);
    }

    public function getAdherentCommitteesSupervisor(Adherent $adherent): array
    {
        return $this->doGetAdherentCommittees($adherent, true);
    }

    private function doGetAdherentCommittees(Adherent $adherent, $onlySupervisor = false): array
    {
        // Prevent SQL query if the adherent doesn't follow any committees yet.
        if (!count($memberships = $adherent->getMemberships())) {
            return [];
        }

        if (true === $onlySupervisor) {
            $memberships = $memberships->getCommitteeSupervisorMemberships();
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
            });

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
        return $this->getAdherentRepository()->findOneByUuid($committee->getCreatedBy());
    }

    public function getCommitteeReferents(Committee $committee): AdherentCollection
    {
        return $this->getAdherentRepository()->findReferentsByCommittee($committee);
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
     * Approves one committee and transforms creator to supervisor.
     *
     * @param Committee $committee
     * @param bool      $flush
     */
    public function approveCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->approved();

        $creator = $this->getAdherentRepository()->findOneByUuid($committee->getCreatedBy());
        $this->changePrivilege($creator, $committee, CommitteeMembership::COMMITTEE_SUPERVISOR, false);

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Pre-approves one committee.
     *
     * @param Committee $committee
     * @param bool      $flush
     */
    public function preApproveCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->preApproved();

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Refuses one committee and transforms supervisor and host to members.
     *
     * @param Committee $committee
     * @param bool      $flush
     */
    public function refuseCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->refused();

        $memberships = $this->getCommitteeMemberships($committee);
        foreach ($memberships as $membership) {
            if ($membership->isSupervisor() || $membership->isHostMember()) {
                $committee = $this->getCommitteeRepository()->findOneByUuid($membership->getCommitteeUuid()->toString());
                $this->changePrivilege($membership->getAdherent(), $committee, CommitteeMembership::COMMITTEE_FOLLOWER, false);
            }
        }

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    /**
     * Pre-refuses one committee.
     *
     * @param Committee $committee
     * @param bool      $flush
     */
    public function preRefuseCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->preRefused();

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
                $this->followCommittee($adherent, $committee, false);
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

    private function getAdherentRepository(): AdherentRepository
    {
        return $this->registry->getRepository(Adherent::class);
    }

    public function countApprovedCommittees(): int
    {
        return $this->getCommitteeRepository()->countApprovedCommittees();
    }

    public function changePrivilege(Adherent $adherent, Committee $committee, string $privilege, bool $flush = true): void
    {
        CommitteeMembership::checkPrivilege($privilege);

        if (!$committeeMembership = $this->getCommitteeMembership($adherent, $committee)) {
            return;
        }

        if (CommitteeMembership::COMMITTEE_SUPERVISOR === $privilege) {
            // We can't have more than 1 supervisors per committee
            if ($this->countCommitteeSupervisors($committee)) {
                throw CommitteeMembershipException::createNotPromotableSupervisorPrivilegeException($committeeMembership->getUuid());
            }

            // Adherent can't be supervisor of multiple committees
            if ($committeeSupervisor = $this->getMembershipRepository()->superviseCommittee($adherent)) {
                throw CommitteeMembershipException::createNotPromotableSupervisorPrivilegeForSupervisorException($committeeMembership->getUuid(), $adherent->getEmailAddress());
            }

            // We can't add a supervisor if committee is not approuved
            if ($committeeSupervisor = $this->getMembershipRepository()->superviseCommittee($adherent)) {
                throw CommitteeMembershipException::createNotPromotableSupervisorPrivilegeForNotApprovedCommitteeException($committeeMembership->getUuid(), $committee->getName());
            }
        }

        $committeeMembership->setPrivilege($privilege);

        if ($flush) {
            $this->getManager()->flush();
        }
    }

    public function getCoordinatorCommittees(Adherent $coordinator, CommitteeFilters &$filters): array
    {
        $committees = $this->getCommitteeRepository()->findManagedByCoordinator($coordinator, $filters);

        foreach ($committees as $committee) {
            $creator = $this->getCommitteeCreator($committee);
            $committee->setCreator($creator);
        }

        return $committees;
    }
}
