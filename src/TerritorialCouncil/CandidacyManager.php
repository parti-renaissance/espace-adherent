<?php

namespace App\TerritorialCouncil;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\CandidacyInvitation;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Repository\TerritorialCouncil\CandidacyInvitationRepository;
use App\TerritorialCouncil\Event\CandidacyInvitationEvent;
use App\VotingPlatform\Event\BaseCandidacyEvent;
use App\VotingPlatform\Event\TerritorialCouncilCandidacyEvent;
use App\VotingPlatform\Events as VotingPlatformEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CandidacyManager
{
    private $entityManager;
    private $dispatcher;
    private $invitationRepository;

    public function __construct(
        ObjectManager $entityManager,
        EventDispatcherInterface $eventDispatcher,
        CandidacyInvitationRepository $repository
    ) {
        $this->entityManager = $entityManager;
        $this->dispatcher = $eventDispatcher;
        $this->invitationRepository = $repository;
    }

    public function updateCandidature(Candidacy $candidacy): void
    {
        $isCreation = false;

        if (!$candidacy->getId()) {
            $isCreation = true;
            $this->entityManager->persist($candidacy);
        }

        if ($candidacy->isConfirmed()) {
            $candidacy->getBinome()->updateFromBinome();
        }

        $this->entityManager->flush();

        if ($isCreation) {
            $this->dispatcher->dispatch(
                VotingPlatformEvents::CANDIDACY_CREATED,
                new TerritorialCouncilCandidacyEvent($candidacy)
            );
        } else {
            $this->dispatcher->dispatch(
                VotingPlatformEvents::CANDIDACY_UPDATED,
                new TerritorialCouncilCandidacyEvent($candidacy)
            );
        }
    }

    public function removeCandidacy(Candidacy $candidacy): void
    {
        $this->entityManager->remove($candidacy);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(VotingPlatformEvents::CANDIDACY_REMOVED, new BaseCandidacyEvent($candidacy));
    }

    public function acceptInvitation(CandidacyInvitation $invitation, Candidacy $acceptedBy): void
    {
        $invitation->accept();

        $invitation->getCandidacy()->updateFromBinome();
        $invitation->getCandidacy()->confirm();
        $acceptedBy->confirm();

        $this->updateCandidature($acceptedBy);

        $this->dispatcher->dispatch(
            Events::CANDIDACY_INVITATION_ACCEPT,
            new CandidacyInvitationEvent($invitation->getCandidacy(), $invitation)
        );

        foreach ($this->invitationRepository->findAllPendingForMembership($invitation->getMembership(), $acceptedBy->getElection()) as $invitation) {
            $invitation->decline();

            $this->dispatcher->dispatch(
                Events::CANDIDACY_INVITATION_DECLINE,
                new CandidacyInvitationEvent($invitation->getCandidacy(), $invitation)
            );
        }

        $this->entityManager->flush();
    }

    public function declineInvitation(CandidacyInvitation $invitation): void
    {
        $invitation->decline();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(
            Events::CANDIDACY_INVITATION_DECLINE,
            new CandidacyInvitationEvent($invitation->getCandidacy(), $invitation)
        );
    }

    public function updateInvitation(
        CandidacyInvitation $invitation,
        Candidacy $candidacy,
        TerritorialCouncilMembership $previouslyInvitedMembership = null
    ): void {
        $invitation->resetStatus();

        $this->updateCandidature($candidacy);

        $this->dispatcher->dispatch(
            Events::CANDIDACY_INVITATION_UPDATE,
            new CandidacyInvitationEvent($candidacy, $invitation, $previouslyInvitedMembership)
        );
    }
}
