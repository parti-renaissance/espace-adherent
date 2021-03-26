<?php

namespace App\VotingPlatform\Listener;

use App\Committee\Election\CandidacyManager as CommitteeCandidacyManager;
use App\Entity\CommitteeMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\TerritorialCouncil\CandidacyManager as CoTerrCandidacyManager;
use App\VotingPlatform\Event\CandidacyInvitationEvent;
use App\VotingPlatform\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeclinePendingCandidacyInvitationsListener implements EventSubscriberInterface
{
    private $committeeCandidacyManager;
    private $coTerrCandidacyManager;
    private $entityManager;
    private $eventDispatcher;

    public function __construct(
        CommitteeCandidacyManager $committeeCandidacyManager,
        CoTerrCandidacyManager $coTerrCandidacyManager,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->committeeCandidacyManager = $committeeCandidacyManager;
        $this->coTerrCandidacyManager = $coTerrCandidacyManager;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
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

        /** @var CommitteeCandidacyManager|CoTerrCandidacyManager $manager */
        if ($election->getDesignation()->isCopolType()) {
            $manager = $this->coTerrCandidacyManager;
        } else {
            $manager = $this->committeeCandidacyManager;
        }

        // Decline my other invitations
        foreach ($manager->getInvitationsToDecline($membership, $election) as $invitation) {
            $manager->declineInvitation($invitation);
        }

        // Remove my sent invitations
        /** @var CommitteeMembership|TerritorialCouncilMembership $membership */
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
