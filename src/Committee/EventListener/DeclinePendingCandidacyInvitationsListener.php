<?php

namespace App\Committee\EventListener;

use App\Committee\Election\CandidacyManager;
use App\Entity\CommitteeCandidacyInvitation;
use App\Repository\CommitteeCandidacyInvitationRepository;
use App\TerritorialCouncil\Events;
use App\VotingPlatform\Event\CandidacyInvitationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeclinePendingCandidacyInvitationsListener implements EventSubscriberInterface
{
    private $repository;
    private $candidacyManager;

    public function __construct(CandidacyManager $candidacyManager, CommitteeCandidacyInvitationRepository $repository)
    {
        $this->candidacyManager = $candidacyManager;
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CANDIDACY_INVITATION_ACCEPT => 'onCandidacyInvitationAccept',
        ];
    }

    public function onCandidacyInvitationAccept(CandidacyInvitationEvent $event): void
    {
        if (!($invitation = $event->getInvitation()) instanceof CommitteeCandidacyInvitation) {
            return;
        }

        $candidacy = $event->getCandidacy();

        foreach ($this->repository->findAllPendingForMembership($invitation->getMembership(), $candidacy->getElection()) as $invitation) {
            $this->candidacyManager->declineInvitation($invitation);
        }
    }
}
