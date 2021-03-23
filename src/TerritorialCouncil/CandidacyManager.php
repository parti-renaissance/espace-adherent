<?php

namespace App\TerritorialCouncil;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\CandidacyInvitation;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\VotingPlatform\Designation\CandidacyInvitationInterface;
use App\Repository\TerritorialCouncil\CandidacyInvitationRepository;
use App\VotingPlatform\Event\BaseCandidacyEvent;
use App\VotingPlatform\Event\CandidacyInvitationEvent;
use App\VotingPlatform\Event\TerritorialCouncilCandidacyEvent;
use App\VotingPlatform\Events as VotingPlatformEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

        $candidacy->syncWithOtherCandidacies();

        $this->entityManager->flush();

        if ($isCreation) {
            $this->dispatcher->dispatch(new TerritorialCouncilCandidacyEvent($candidacy), VotingPlatformEvents::CANDIDACY_CREATED);
        } else {
            $this->dispatcher->dispatch(new TerritorialCouncilCandidacyEvent($candidacy), VotingPlatformEvents::CANDIDACY_UPDATED);
        }
    }

    public function removeCandidacy(Candidacy $candidacy): void
    {
        $this->entityManager->remove($candidacy);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new BaseCandidacyEvent($candidacy), VotingPlatformEvents::CANDIDACY_REMOVED);
    }

    public function acceptInvitation(CandidacyInvitation $invitation, Candidacy $acceptedBy): void
    {
        $invitation->accept();

        /** @var Candidacy $invitedBy */
        $invitedBy = $invitation->getCandidacy();
        $invitedBy->setFaithStatement($acceptedBy->getFaithStatement());
        $invitedBy->setIsPublicFaithStatement($acceptedBy->isPublicFaithStatement());

        $election = $invitedBy->getElection();

        if ($invitedBy->hasOnlyAcceptedInvitations()) {
            $invitedBy->confirm();

            foreach ($invitedBy->getInvitations() as $otherInvitation) {
                if ($invitation === $otherInvitation) {
                    $candidacy = $acceptedBy;
                } else {
                    /** @var TerritorialCouncilMembership $membership */
                    $membership = $otherInvitation->getMembership();
                    $candidacy = $membership->getCandidacyForElection($election);
                }

                $invitedBy->candidateWith($candidacy);
                $candidacy->confirm();
            }

            $invitedBy->syncWithOtherCandidacies();
        }

        $this->updateCandidature($acceptedBy);

        $this->dispatcher->dispatch(
            new CandidacyInvitationEvent($invitation->getCandidacy(), [$invitation]),
            Events::CANDIDACY_INVITATION_ACCEPT
        );

        foreach ($this->invitationRepository->findAllPendingForMembership($invitation->getMembership(), $acceptedBy->getElection()) as $invitation) {
            $invitation->decline();

            $this->dispatcher->dispatch(
                new CandidacyInvitationEvent($invitation->getCandidacy(), [$invitation]),
                Events::CANDIDACY_INVITATION_DECLINE
            );
        }

        $this->entityManager->flush();
    }

    public function declineInvitation(CandidacyInvitation $invitation): void
    {
        $invitation->decline();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(
            new CandidacyInvitationEvent($invitation->getCandidacy(), [$invitation]),
            Events::CANDIDACY_INVITATION_DECLINE
        );
    }

    /**
     * @param TerritorialCouncilMembership[] $previouslyInvitedMemberships
     * @param CandidacyInvitationInterface[] $invitations
     */
    public function updateInvitation(
        Candidacy $candidacy,
        array $invitations,
        array $previouslyInvitedMemberships = []
    ): void {
        array_walk($invitations, function (CandidacyInvitation $invitation) {
            $invitation->resetStatus();
        });

        $this->updateCandidature($candidacy);

        $this->dispatcher->dispatch(
            new CandidacyInvitationEvent($candidacy, $invitations, $previouslyInvitedMemberships),
            Events::CANDIDACY_INVITATION_UPDATE
        );
    }

    /**
     * @param TerritorialCouncilMembership[] $previouslyInvitedMemberships
     */
    public function saveSingleCandidature(Candidacy $candidacy, array $previouslyInvitedMemberships = []): void
    {
        if (!$candidacy->isCouncilor()) {
            throw new \RuntimeException(sprintf('Candidacy "%s" is not allowed to candidate without another member.', $candidacy->getUuid()));
        }

        $candidacy->confirm();

        $this->updateCandidature($candidacy);

        $this->dispatcher->dispatch(
            new CandidacyInvitationEvent($candidacy, [], $previouslyInvitedMemberships),
            Events::CANDIDACY_INVITATION_UPDATE
        );
    }
}
