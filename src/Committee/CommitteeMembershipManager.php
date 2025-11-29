<?php

declare(strict_types=1);

namespace App\Committee;

use App\Committee\Event\FollowCommitteeEvent;
use App\Committee\Event\UnfollowCommitteeEvent;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
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
        if ($membership = $adherent->getCommitteeMembership()) {
            if ($membership->getCommittee()->getId() === $committee->getId()) {
                return;
            }

            $this->unfollowCommittee($membership);
        }

        $this->entityManager->persist($membership = $adherent->followCommittee($committee));
        $membership->setTrigger($trigger);

        $committee->updateMembersCount(
            true,
            $adherent->isRenaissanceSympathizer(),
            $adherent->isRenaissanceAdherent(),
            $adherent->hasActiveMembership()
        );

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new FollowCommitteeEvent($membership), UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE);
    }

    /**
     * @return CommitteeMembership[]
     */
    public function getCommitteeMemberships(Committee $committee): array
    {
        return $this->committeeMembershipRepository->findCommitteeMemberships($committee);
    }

    public function unfollowCommittee(CommitteeMembership $membership): void
    {
        $adherent = $membership->getAdherent();
        $adherent->setCommitteeMembership(null);
        $this->entityManager->remove($membership);

        $membership->getCommittee()->updateMembersCount(
            false,
            $adherent->isRenaissanceSympathizer(),
            $adherent->isRenaissanceAdherent(),
            $adherent->hasActiveMembership()
        );

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UnfollowCommitteeEvent($membership), UserEvents::USER_UPDATE_COMMITTEE_PRIVILEGE);
    }
}
