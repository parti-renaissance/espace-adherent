<?php

namespace AppBundle\Committee;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Collection\CommitteeMembershipCollection;
use AppBundle\Committee\Event\FollowCommitteeEvent;
use AppBundle\Committee\Event\UnfollowCommitteeEvent;
use AppBundle\Coordinator\Filter\CommitteeFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeCandidacy;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\Reporting\CommitteeMembershipAction;
use AppBundle\Entity\Reporting\CommitteeMembershipHistory;
use AppBundle\Events;
use AppBundle\Exception\CommitteeMembershipException;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Membership\UserEvents;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CommitteeFeedItemRepository;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\VotingPlatform\Event\CommitteeCandidacyEvent;
use AppBundle\VotingPlatform\Events as VotingPlatformEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommitteeManager
{
    const EXCLUDE_HOSTS = false;
    const INCLUDE_HOSTS = true;

    private const COMMITTEE_PROPOSALS_COUNT = 3;

    private $registry;
    private $dispatcher;

    public function __construct(RegistryInterface $registry, EventDispatcherInterface $dispatcher)
    {
        $this->registry = $registry;
        $this->dispatcher = $dispatcher;
    }

    public function isPromotableHost(Adherent $adherent, Committee $committee): bool
    {
        if (!$membership = $this->getMembershipRepository()->findMembership($adherent, $committee)) {
            return false;
        }

        return $membership->isFollower();
    }

    public function isDemotableHost(Adherent $adherent, Committee $committee): bool
    {
        if (!$membership = $this->getMembershipRepository()->findMembership($adherent, $committee)) {
            return false;
        }

        return $membership->isHostMember();
    }

    public function isCommitteeHost(Adherent $adherent): bool
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related committee memberships entities.
        if ($adherent->hasLoadedMemberships() && $adherent->isHost()) {
            return true;
        }

        return $this->getMembershipRepository()->hostCommittee($adherent);
    }

    public function hostCommittee(Adherent $adherent, Committee $committee): bool
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related committee memberships entities.
        if ($adherent->hasLoadedMemberships() && $adherent->isHostOf($committee)) {
            return true;
        }

        return $this->getMembershipRepository()->hostCommittee($adherent, $committee);
    }

    public function superviseCommittee(Adherent $adherent, Committee $committee): bool
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related committee memberships entities.
        if ($adherent->hasLoadedMemberships() && $adherent->isSupervisorOf($committee)) {
            return true;
        }

        return $this->getMembershipRepository()->superviseCommittee($adherent, $committee);
    }

    public function getAdherentCommittees(Adherent $adherent): array
    {
        // Prevent SQL query if the adherent doesn't follow any committees yet.
        if (0 === \count($memberships = $adherent->getMemberships())) {
            return [];
        }

        $committees = $this
            ->getCommitteeRepository()
            ->findCommittees($memberships->getCommitteeUuids())
            ->getOrderedCommittees($adherent->getMemberships())
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
        return $this->getMembershipRepository()->countHostMembers($committee);
    }

    public function countCommitteeSupervisors(Committee $committee): int
    {
        return $this->getMembershipRepository()->countSupervisorMembers($committee);
    }

    public function getCommitteeHosts(Committee $committee): AdherentCollection
    {
        return $this->getMembershipRepository()->findHostMembers($committee);
    }

    public function getCommitteeCreator(Committee $committee): ?Adherent
    {
        return $committee->getCreatedBy() ? $this->getAdherentRepository()->findByUuid($committee->getCreatedBy()) : null;
    }

    public function getCommitteeReferents(Committee $committee): AdherentCollection
    {
        return $this->getAdherentRepository()->findReferentsByCommittee($committee);
    }

    public function getCommitteeFollowers(
        Committee $committee,
        bool $withHosts = self::INCLUDE_HOSTS
    ): AdherentCollection {
        return $this->getMembershipRepository()->findFollowers($committee, $withHosts);
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
     * @param int $limit
     *
     * @return Committee[]
     */
    public function getNearbyCommittees(Coordinates $coordinates, $limit = self::COMMITTEE_PROPOSALS_COUNT): array
    {
        $data = [];
        $committees = $this->getCommitteeRepository()->findNearbyCommittees($coordinates, $limit);

        foreach ($committees as $committee) {
            $data[(string) $committee->getUuid()] = $committee;
        }

        return $data;
    }

    public function getCommitteeMembers(Committee $committee): AdherentCollection
    {
        return $this->getMembershipRepository()->findMembers($committee);
    }

    /**
     * @return CommitteeMembershipCollection|CommitteeMembership[]
     */
    public function getCommitteeMemberships(Committee $committee): CommitteeMembershipCollection
    {
        return $this->getMembershipRepository()->findCommitteeMemberships($committee);
    }

    public function getCommitteeMembership(Adherent $adherent, Committee $committee): ?CommitteeMembership
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related committee memberships entities.
        if ($adherent->hasLoadedMemberships()) {
            return $adherent->getMembershipFor($committee);
        }

        return $this->getMembershipRepository()->findMembership($adherent, $committee);
    }

    /**
     * @return CommitteeMembership[]
     */
    public function getCommitteeMembershipsForAdherent(Adherent $adherent): array
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related committee memberships entities.
        if ($adherent->hasLoadedMemberships()) {
            return $adherent->getMemberships()->getMembershipsForApprovedCommittees();
        }

        return $this->getMembershipRepository()->findMemberships($adherent)->getMembershipsForApprovedCommittees();
    }

    /**
     * Promotes an adherent to be a host of a committee.
     */
    public function promote(Adherent $adherent, Committee $committee): void
    {
        $membership = $this->getMembershipRepository()->findMembership($adherent, $committee);

        if (!$membership->isPromotableHost()) {
            throw CommitteeMembershipException::createNotPromotableHostPrivilegeException($membership->getUuid());
        }

        $this->changePrivilegeOnMembership($membership, CommitteeMembership::COMMITTEE_HOST, true);
    }

    /**
     * Promotes an adherent to be a host of a committee.
     */
    public function demote(Adherent $adherent, Committee $committee): void
    {
        $membership = $this->getMembershipRepository()->findMembership($adherent, $committee);

        if (!$membership->isDemotableHost()) {
            throw CommitteeMembershipException::createNotDemotableFollowerPrivilegeException($membership->getUuid());
        }

        $this->changePrivilegeOnMembership($membership, CommitteeMembership::COMMITTEE_FOLLOWER, true);
    }

    /**
     * Approves one committee and transforms creator to supervisor.
     */
    public function approveCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->approved();

        /** @var Adherent $creator */
        $creator = $this->getAdherentRepository()->findOneByUuid($committee->getCreatedBy());
        $this->changePrivilege($creator, $committee, CommitteeMembership::COMMITTEE_SUPERVISOR, false);

        if ($flush) {
            $this->getManager()->flush();
        }

        $this->dispatcher->dispatch(Events::COMMITTEE_UPDATED, new CommitteeEvent($committee));
    }

    /**
     * Pre-approves one committee.
     */
    public function preApproveCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->preApproved();

        if ($flush) {
            $this->getManager()->flush();
        }

        $this->dispatcher->dispatch(Events::COMMITTEE_UPDATED, new CommitteeEvent($committee));
    }

    /**
     * Refuses one committee and transforms supervisor and host to members.
     */
    public function refuseCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->refused();

        $memberships = $this->getCommitteeMemberships($committee);
        foreach ($memberships as $membership) {
            if ($membership->isSupervisor() || $membership->isHostMember()) {
                $committee = $this->getCommitteeRepository()->findOneByUuid($membership->getCommittee()->getUuidAsString());
                $this->changePrivilege($membership->getAdherent(), $committee, CommitteeMembership::COMMITTEE_FOLLOWER, false);
            }
        }

        if ($flush) {
            $this->getManager()->flush();
        }

        $this->dispatcher->dispatch(Events::COMMITTEE_UPDATED, new CommitteeEvent($committee));
    }

    /**
     * Pre-refuses one committee.
     */
    public function preRefuseCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->preRefused();

        if ($flush) {
            $this->getManager()->flush();
        }

        $this->dispatcher->dispatch(Events::COMMITTEE_UPDATED, new CommitteeEvent($committee));
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
        $manager->persist($membership = $adherent->followCommittee($committee));

        $manager->persist($this->createCommitteeMembershipHistory($membership, CommitteeMembershipAction::JOIN()));

        if ($flush) {
            $manager->flush();
        }

        $this->dispatcher->dispatch(Events::COMMITTEE_NEW_FOLLOWER, new FollowCommitteeEvent($adherent, $committee));
        $this->dispatcher->dispatch(Events::COMMITTEE_UPDATED, new CommitteeEvent($committee));
    }

    /**
     * Makes an adherent unfollow one committee.
     *
     * @param Adherent  $adherent  The follower
     * @param Committee $committee The committee to follow
     */
    public function unfollowCommittee(Adherent $adherent, Committee $committee): void
    {
        if ($adherent->hasLoadedMemberships()) {
            $membership = $adherent->getMembershipFor($committee);
        } else {
            $membership = $this->getMembershipRepository()->findMembership($adherent, $committee);
        }

        if ($membership) {
            $this->doUnfollowCommittee($membership, $committee);
        }
    }

    /**
     * Makes an adherent vote in one committee.
     */
    public function enableVoteInMembership(CommitteeMembership $membership, Adherent $adherent): void
    {
        if ($existingVotingMembership = $adherent->getMemberships()->getVotingCommitteeMembership()) {
            $this->disableVoteInMembership($existingVotingMembership);
        }

        $membership->enableVote();
        $this->getManager()->flush();
    }

    /**
     * Makes an adherent cease voting in one committee.
     */
    public function disableVoteInMembership(CommitteeMembership $membership): void
    {
        if ($membership->getCommitteeCandidacy()) {
            throw CommitteeMembershipException::createRunningCommitteeCandidacyException($membership->getUuid(), $committee->getName);
        }

        $membership->disableVote();
        $this->getManager()->flush();
    }

    public function candidateInCommittee(CommitteeCandidacy $candidacy, Adherent $adherent, Committee $committee): bool
    {
        $membership = $this->getCommitteeMembership($adherent, $committee);

        if ($membership && $membership->isVotingCommittee()) {
            $membership->setCommitteeCandidacy($candidacy);

            $this->getManager()->flush();

            $this->dispatcher->dispatch(
                VotingPlatformEvents::CANDIDACY_CREATED,
                new CommitteeCandidacyEvent($candidacy, $committee, $adherent, $this->getCommitteeSupervisor($committee))
            );

            return true;
        }

        return false;
    }

    public function removeCandidacy(Adherent $adherent, Committee $committee): void
    {
        $membership = $this->getCommitteeMembership($adherent, $committee);

        if ($membership && $candidacy = $membership->getCommitteeCandidacy()) {
            // Initialise Doctrine Proxy object
            $candidacy->getCommitteeElection();

            $membership->removeCandidacy();

            $this->getManager()->flush();

            $this->dispatcher->dispatch(
                VotingPlatformEvents::CANDIDACY_REMOVED,
                new CommitteeCandidacyEvent(
                    $candidacy,
                    $committee,
                    $adherent,
                    $this->getCommitteeSupervisor($committee)
                )
            );
        }
    }

    private function doUnfollowCommittee(CommitteeMembership $membership, Committee $committee): void
    {
        $manager = $this->getManager();

        $manager->remove($membership);
        $committee->decrementMembersCount();

        $manager->persist($this->createCommitteeMembershipHistory($membership, CommitteeMembershipAction::LEAVE()));

        $manager->flush();

        $this->dispatcher->dispatch(Events::COMMITTEE_UPDATED, new CommitteeEvent($committee));

        $this->dispatcher->dispatch(
            UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE,
            new UnfollowCommitteeEvent($membership->getAdherent(), $committee)
        );
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

    public function changePrivilege(
        Adherent $adherent,
        Committee $committee,
        string $privilege,
        bool $flush = true
    ): void {
        if (!$committeeMembership = $this->getCommitteeMembership($adherent, $committee)) {
            return;
        }

        $this->changePrivilegeOnMembership($committeeMembership, $privilege, $flush);
    }

    public function getCoordinatorCommittees(Adherent $coordinator, CommitteeFilter $filter): array
    {
        $committees = $this->getCommitteeRepository()->findManagedByCoordinator($coordinator, $filter);

        foreach ($committees as $committee) {
            $creator = $this->getCommitteeCreator($committee);
            $committee->setCreator($creator);
        }

        return $committees;
    }

    public function hasCommitteeInStatus(Adherent $adherent, array $status): bool
    {
        return $this->getCommitteeRepository()->hasCommitteeInStatus($adherent, $status);
    }

    public function getCommitteeSupervisor(Committee $committee): ?Adherent
    {
        return $this->getMembershipRepository()->findSupervisor($committee);
    }

    public function getCommitteesByCoordinatesAndCountry(
        Coordinates $coordinates,
        string $country,
        string $postalCode,
        int $count = self::COMMITTEE_PROPOSALS_COUNT
    ): array {
        $postalCodePrefix = \array_key_exists(substr($postalCode, 0, 3), FranceCitiesBundle::DOMTOM_INSEE_CODE)
            ? substr($postalCode, 0, 3)
            : null
        ;

        return $this->getCommitteeRepository()->findNearbyCommitteesFilteredByCountry(
            $coordinates,
            $country,
            $postalCodePrefix,
            $count
        );
    }

    public function getLastApprovedCommitteesAndMembers(int $count = self::COMMITTEE_PROPOSALS_COUNT): array
    {
        return $this->getCommitteeRepository()->findLastApprovedCommittees($count);
    }

    private function createCommitteeMembershipHistory(
        CommitteeMembership $membership,
        CommitteeMembershipAction $action
    ): CommitteeMembershipHistory {
        return new CommitteeMembershipHistory($membership, $action);
    }

    private function changePrivilegeOnMembership(
        CommitteeMembership $membership,
        string $privilege,
        bool $flush = true
    ): void {
        CommitteeMembership::checkPrivilege($privilege);

        $adherent = $membership->getAdherent();

        if (CommitteeMembership::COMMITTEE_SUPERVISOR === $privilege) {
            // We can't have more than 1 supervisors per committee
            if ($this->countCommitteeSupervisors($committee = $membership->getCommittee())) {
                throw CommitteeMembershipException::createNotPromotableSupervisorPrivilegeException($membership->getUuid());
            }

            // Adherent can't be supervisor of multiple committees
            if ($this->getMembershipRepository()->superviseCommittee($adherent)) {
                throw CommitteeMembershipException::createNotPromotableSupervisorPrivilegeForSupervisorException($membership->getUuid(), $adherent->getEmailAddress());
            }

            // We can't add a supervisor if committee is not approved
            if ($this->getMembershipRepository()->superviseCommittee($adherent)) {
                throw CommitteeMembershipException::createNotPromotableSupervisorPrivilegeForNotApprovedCommitteeException($membership->getUuid(), $committee->getName());
            }

            if ($adherent->getMemberships()->getCommitteeCandidacyMembership()) {
                throw CommitteeMembershipException::createNotPromotableSupervisorPrivilegeForCandidateMember($membership->getUuid());
            }

            if ($votingMembership = $adherent->getMemberships()->getVotingCommitteeMembership()) {
                $votingMembership->disableVote();
                $this->getManager()->flush();
            }

            $membership->enableVote();
        }

        $membership->setPrivilege($privilege);

        if ($flush) {
            $this->getManager()->flush();
        }

        $this->dispatcher->dispatch(UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE, new FollowCommitteeEvent($adherent));
    }

    public function getCandidacy(Adherent $adherent, Committee $committee): ?CommitteeCandidacy
    {
        $membership = $this->getCommitteeMembership($adherent, $committee);

        return $membership->getCommitteeCandidacy();
    }

    public function saveCandidacy(CommitteeCandidacy $candidacy)
    {
        if (!$candidacy->getId()) {
            $this->getManager()->persist($candidacy);
        }

        $this->getManager()->flush();
    }
}
