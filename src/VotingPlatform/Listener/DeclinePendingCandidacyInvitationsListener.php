<?php

declare(strict_types=1);

namespace App\VotingPlatform\Listener;

use App\Committee\Election\CandidacyManager as CommitteeCandidacyManager;
use App\Entity\CommitteeMembership;
use App\VotingPlatform\Event\CandidacyInvitationEvent;
use App\VotingPlatform\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeclinePendingCandidacyInvitationsListener implements EventSubscriberInterface
{
    private $committeeCandidacyManager;
    private $entityManager;
    private $eventDispatcher;

    public function __construct(
        CommitteeCandidacyManager $committeeCandidacyManager,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->committeeCandidacyManager = $committeeCandidacyManager;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CANDIDACY_INVITATION_ACCEPT => 'onCandidacyInvitationAccept',
        ];
    }

    public function onCandidacyInvitationAccept(CandidacyInvitationEvent $event): void
    {
        $invitation = current($event->getInvitations());
        $membership = $invitation->getMembership();
        $election = $event->getCandidacy()->getElection();

        // Decline my other invitations
        foreach ($this->committeeCandidacyManager->getInvitationsToDecline($membership, $election) as $invitation) {
            $this->committeeCandidacyManager->declineInvitation($invitation);
        }

        // Remove my sent invitations
        /** @var CommitteeMembership $membership */
        $this->entityManager->refresh($membership);

        $candidacy = $membership->getCandidacyForElection($election);

        foreach ($candidacy->getInvitations() as $invitation) {
            $candidacy->removeInvitation($invitation);

            $this->entityManager->remove($invitation);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(
                new CandidacyInvitationEvent($candidacy, null, [$invitation]),
                Events::CANDIDACY_INVITATION_REMOVE
            );
        }
    }
}
