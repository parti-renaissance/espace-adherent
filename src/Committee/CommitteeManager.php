<?php

namespace App\Committee;

use App\Address\AddressInterface;
use App\Collection\AdherentCollection;
use App\Committee\Event\ApproveCommitteeEvent;
use App\Committee\Event\EditCommitteeEvent;
use App\Committee\Event\FollowCommitteeEvent;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Geo\Zone;
use App\Geo\ZoneMatcher;
use App\Membership\UserEvents;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CommitteeMembershipManager $committeeMembershipManager,
        private readonly CommitteeRepository $committeeRepository,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ZoneMatcher $zoneMatcher,
    ) {
    }

    public function findCommitteeByAddress(AddressInterface $address): ?Committee
    {
        if (!$zones = $this->zoneMatcher->match($address)) {
            return null;
        }

        foreach ($this->orderZones($zones, true, Zone::COMMITTEE_TYPES) as $zone) {
            if ($committee = current($this->committeeRepository->findInZones([$zone], false))) {
                return $committee;
            }
        }

        return null;
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
        return $committee->getCreatedBy() ? $this->getAdherentRepository()->findOneByUuid($committee->getCreatedBy()) : null;
    }

    /**
     * Approves one committee
     */
    public function approveCommittee(Committee $committee): void
    {
        $committee->approved();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EditCommitteeEvent($committee));
        $this->dispatcher->dispatch(new ApproveCommitteeEvent($committee));
    }

    /**
     * Refuses one committee and transforms supervisor and host to members.
     * Also add end date to committee adherent mandates.
     */
    public function refuseCommittee(Committee $committee, bool $flush = true): void
    {
        $committee->refused();

        $memberships = $this->committeeMembershipManager->getCommitteeMemberships($committee);
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

        $this->dispatcher->dispatch(new EditCommitteeEvent($committee));
    }

    /**
     * Makes an adherent vote in one committee.
     */
    public function enableVoteInMembership(CommitteeMembership $membership, Adherent $adherent): void
    {
        if ($membership->isVotingCommittee()) {
            return;
        }

        if (($existingVotingMembership = $adherent->getCommitteeMembership()) && $existingVotingMembership->isVotingCommittee()) {
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

    private function getCommitteeRepository(): CommitteeRepository
    {
        return $this->entityManager->getRepository(Committee::class);
    }

    private function getAdherentRepository(): AdherentRepository
    {
        return $this->entityManager->getRepository(Adherent::class);
    }

    public function changePrivilege(
        Adherent $adherent,
        Committee $committee,
        string $privilege,
        bool $flush = true,
    ): void {
        if (!$committeeMembership = $adherent->getMembershipFor($committee)) {
            return;
        }

        $this->changePrivilegeOnMembership($committeeMembership, $privilege, $flush);
    }

    private function changePrivilegeOnMembership(
        CommitteeMembership $membership,
        string $privilege,
        bool $flush = true,
    ): void {
        CommitteeMembership::checkPrivilege($privilege);

        $adherent = $membership->getAdherent();

        if (CommitteeMembership::COMMITTEE_HOST === $privilege) {
            if ($adherent->isSupervisorOf($membership->getCommittee())) {
                throw new \RuntimeException('A supervisor cannot be promoted to host.');
            }

            // We can't have more than 2 hosts per committee
            if ($this->countCommitteeHosts($membership->getCommittee(), true) > 1) {
                throw new \RuntimeException('A committee cannot have more than 2 hosts.');
            }
        }

        $membership->setPrivilege($privilege);

        if ($flush) {
            $this->entityManager->flush();
        }

        $this->dispatcher->dispatch(new FollowCommitteeEvent($membership), UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE);
    }

    /**
     * @return Zone[]
     */
    private function orderZones(array $zones, bool $flattenParents = false, array $types = []): array
    {
        $flattenZones = $flattenParents ? $this->zoneMatcher->flattenZones($zones, $types) : $zones;

        usort(
            $flattenZones,
            fn (Zone $a, Zone $b) => array_search($a->getType(), Zone::COMMITTEE_TYPES) <=> array_search($b->getType(), Zone::COMMITTEE_TYPES)
        );

        return $flattenZones;
    }
}
