<?php

namespace App\Committee;

use App\Admin\Committee\CommitteeAdherentMandateTypeEnum;
use App\Collection\AdherentCollection;
use App\Collection\CommitteeMembershipCollection;
use App\Committee\Event\FollowCommitteeEvent;
use App\Committee\Event\UnfollowCommitteeEvent;
use App\Coordinator\Filter\CommitteeFilter;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\Committee;
use App\Entity\CommitteeFeedItem;
use App\Entity\CommitteeMembership;
use App\Entity\Reporting\CommitteeMembershipAction;
use App\Entity\Reporting\CommitteeMembershipHistory;
use App\Events;
use App\Exception\CommitteeMembershipException;
use App\Geocoder\Coordinates;
use App\Intl\FranceCitiesBundle;
use App\Membership\UserEvents;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeFeedItemRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\CommitteeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeManager
{
    public const EXCLUDE_HOSTS = false;
    public const INCLUDE_HOSTS = true;

    private const COMMITTEE_PROPOSALS_COUNT = 3;

    private $entityManager;
    private $dispatcher;
    private $mandateRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        CommitteeAdherentMandateRepository $mandateRepository
    ) {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $this->mandateRepository = $mandateRepository;
    }

    public function isPromotableHost(Adherent $adherent, Committee $committee): bool
    {
        if (!$membership = $this->getMembershipRepository()->findMembership($adherent, $committee)) {
            return false;
        }

        return $membership->isPromotableHost();
    }

    public function isDemotableHost(Adherent $adherent, Committee $committee): bool
    {
        if (!$membership = $this->getMembershipRepository()->findMembership($adherent, $committee)) {
            return false;
        }

        return $membership->isDemotableHost();
    }

    public function getTimeline(Committee $committee, int $limit = 30, int $firstResultIndex = 0): Paginator
    {
        return $this
            ->getCommitteeFeedItemRepository()
            ->findPaginatedMostRecentFeedItems((string) $committee->getUuid(), $limit, $firstResultIndex)
        ;
    }

    public function countCommitteeHosts(Committee $committee, bool $withoutSupervisors = false): int
    {
        return $this->getAdherentRepository()->countCommitteeHosts($committee, $withoutSupervisors);
    }

    public function getCommitteeHosts(Committee $committee, bool $withoutSupervisors = false): AdherentCollection
    {
        return $this->getAdherentRepository()->findCommitteeHosts($committee, $withoutSupervisors);
    }

    public function getCommitteeCreator(Committee $committee): ?Adherent
    {
        return $committee->getCreatedBy() ? $this->getAdherentRepository()->findByUuid($committee->getCreatedBy()) : null;
    }

    public function getCommitteeReferents(Committee $committee): AdherentCollection
    {
        return $this->getAdherentRepository()->findReferentsByCommittee($committee);
    }

    public function getOptinCommitteeFollowers(Committee $committee): AdherentCollection
    {
        return $this->getMembershipRepository()->findForHostEmail($committee);
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
     * Approves one committee
     */
    public function approveCommittee(Committee $committee): void
    {
        $committee->approved();

        foreach ($committee->getProvisionalSupervisors() as $provisionalSupervisor) {
            $adherent = $provisionalSupervisor->getAdherent();

            if ($adherent->getMembershipFor($committee)) {
                continue;
            }

            $this->followCommittee($adherent, $committee);
        }

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_UPDATED);
        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_APPROVED);
    }

    /**
     * Pre-approves one committee.
     */
    public function preApproveCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->preApproved();

        if ($flush) {
            $this->entityManager->flush();
        }

        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_UPDATED);
    }

    /**
     * Refuses one committee and transforms supervisor and host to members.
     * Also add end date to committee adherent mandates.
     */
    public function refuseCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->refused();

        $memberships = $this->getCommitteeMemberships($committee);
        foreach ($memberships as $membership) {
            if ($membership->isHostMember()) {
                $committee = $this->getCommitteeRepository()->findOneByUuid($membership->getCommittee()->getUuidAsString());
                $this->changePrivilege($membership->getAdherent(), $committee, CommitteeMembership::COMMITTEE_FOLLOWER, false);
            }
        }

        /** @var CommitteeAdherentMandate $mandate */
        foreach ($committee->getAdherentMandates() as $mandate) {
            if (!$mandate->isEnded()) {
                $mandate->setFinishAt(new \DateTime());
            }
        }

        if ($flush) {
            $this->entityManager->flush();
        }

        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_UPDATED);
    }

    /**
     * Pre-refuses one committee.
     */
    public function preRefuseCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->preRefused();

        if ($flush) {
            $this->entityManager->flush();
        }

        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_UPDATED);
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

        $this->entityManager->flush();
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
        $this->entityManager->persist($membership = $adherent->followCommittee($committee));

        $this->entityManager->persist($this->createCommitteeMembershipHistory($membership, CommitteeMembershipAction::JOIN()));

        if ($flush) {
            $this->entityManager->flush();
        }

        $this->dispatcher->dispatch(new FollowCommitteeEvent($adherent, $committee), Events::COMMITTEE_NEW_FOLLOWER);
        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_UPDATED);
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
        if ($membership->isVotingCommittee()) {
            return;
        }

        if ($existingVotingMembership = $adherent->getMemberships()->getVotingCommitteeMembership()) {
            $this->disableVoteInMembership($existingVotingMembership);
        }

        $membership->enableVote();
        $this->entityManager->flush();
    }

    /**
     * Makes an adherent cease voting in one committee.
     */
    public function disableVoteInMembership(CommitteeMembership $membership): void
    {
        $membership->disableVote();
        $this->entityManager->flush();
    }

    private function doUnfollowCommittee(CommitteeMembership $membership, Committee $committee): void
    {
        $this->entityManager->remove($membership);
        $committee->decrementMembersCount();

        $this->entityManager->persist($this->createCommitteeMembershipHistory($membership, CommitteeMembershipAction::LEAVE()));

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_UPDATED);

        $this->dispatcher->dispatch(
            new UnfollowCommitteeEvent($membership->getAdherent(), $committee),
            UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE
        );
    }

    private function getCommitteeRepository(): CommitteeRepository
    {
        return $this->entityManager->getRepository(Committee::class);
    }

    private function getCommitteeFeedItemRepository(): CommitteeFeedItemRepository
    {
        return $this->entityManager->getRepository(CommitteeFeedItem::class);
    }

    private function getMembershipRepository(): CommitteeMembershipRepository
    {
        return $this->entityManager->getRepository(CommitteeMembership::class);
    }

    private function getAdherentRepository(): AdherentRepository
    {
        return $this->entityManager->getRepository(Adherent::class);
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

    public function getAvailableMandateTypesFor(Committee $committee): array
    {
        return array_diff(
            CommitteeAdherentMandateTypeEnum::getTypesForCreation(),
            array_map(function (CommitteeAdherentMandate $mandate) {
                return $mandate->getType();
            }, $this->mandateRepository->findAllActiveMandatesForCommittee($committee)));
    }

    public function hasAvailableMandateTypesFor(Committee $committee): bool
    {
        return \count($this->getAvailableMandateTypesFor($committee)) > 0;
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

        if (CommitteeMembership::COMMITTEE_HOST === $privilege) {
            if ($adherent->isSupervisorOf($membership->getCommittee())) {
                throw CommitteeMembershipException::createNotPromotableHostPrivilegeException($membership->getUuid());
            }

            // We can't have more than 2 hosts per committee
            if ($this->countCommitteeHosts($committee = $membership->getCommittee(), true) > 1) {
                throw CommitteeMembershipException::createNotPromotableHostPrivilegeManyHostsException($membership->getUuid());
            }
        }

        $membership->setPrivilege($privilege);

        if ($flush) {
            $this->entityManager->flush();
        }

        $this->dispatcher->dispatch(new FollowCommitteeEvent($adherent, $membership->getCommittee()), UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE);
    }
}
