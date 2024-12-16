<?php

namespace App\Committee;

use App\Committee\Event\CommitteeEvent;
use App\Committee\Event\FollowCommitteeEvent;
use App\Committee\Event\UnfollowCommitteeEvent;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Events;
use App\Membership\UserEvents;
use App\Repository\CommitteeMembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeMembershipManager
{
    public function __construct(
        private readonly CommitteeMembershipRepository $committeeMembershipRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function followCommittee(
        Adherent $adherent,
        Committee $committee,
        CommitteeMembershipTriggerEnum $trigger,
    ): void {
        $alreadyFollow = false;

        // 1. Comes out of the existing committees
        foreach ($this->committeeMembershipRepository->findMemberships($adherent) as $membership) {
            if ($membership->getCommittee()->getId() !== $committee->getId()) {
                $this->unfollowCommittee($membership);
            } elseif (!$alreadyFollow) {
                $alreadyFollow = true;
            }
        }

        // 2. Follow the new committee
        if ($alreadyFollow) {
            return;
        }

        $this->entityManager->persist($membership = $adherent->followCommittee($committee));
        $membership->setTrigger($trigger);
        //        $this->entityManager->persist($this->createCommitteeMembershipHistory($membership, CommitteeMembershipAction::JOIN()));
        // $committee->updateMembersCount(false, $adherent->isRenaissanceSympathizer(), $adherent->isRenaissanceAdherent());

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new FollowCommitteeEvent($adherent, $committee), Events::COMMITTEE_NEW_FOLLOWER);
        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_UPDATED);
        $this->dispatcher->dispatch(new FollowCommitteeEvent($adherent, $committee), UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE);
    }

    /**
     * @return CommitteeMembership[]
     */
    public function getCommitteeMemberships(Committee $committee): array
    {
        return $this->committeeMembershipRepository->findCommitteeMemberships($committee)->toArray();
    }

    public function unfollowCommittee(CommitteeMembership $membership): void
    {
        $this->entityManager->remove($membership);

        // $committee->updateMembersCount(false, $adherent->isRenaissanceSympathizer(), $adherent->isRenaissanceAdherent());

        //        $this->entityManager->persist($this->createCommitteeMembershipHistory($membership, CommitteeMembershipAction::LEAVE()));

        $this->entityManager->flush();

        //        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_UPDATED);

        $this->dispatcher->dispatch(new UnfollowCommitteeEvent($membership->getAdherent(), $membership->getCommittee()), UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE);
    }

    public function getCommitteeMembership(Adherent $adherent, Committee $committee): ?CommitteeMembership
    {
        // Optimization to prevent a SQL query if the current adherent already
        // has a loaded list of related committee memberships entities.
        if ($adherent->hasLoadedMemberships()) {
            return $adherent->getMembershipFor($committee);
        }

        return $this->committeeMembershipRepository->findMembership($adherent, $committee);
    }
}
