<?php

declare(strict_types=1);

namespace App\Committee\Election;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeCandidacyInvitation;
use App\Entity\CommitteeElection;
use App\Entity\CommitteeMembership;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\CandidacyInvitationInterface;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeCandidacyInvitationRepository;
use App\VotingPlatform\Event\BaseCandidacyEvent;
use App\VotingPlatform\Event\CandidacyInvitationEvent;
use App\VotingPlatform\Event\CommitteeCandidacyEvent;
use App\VotingPlatform\Events as VotingPlatformEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CandidacyManager
{
    private $entityManager;
    private $eventDispatcher;
    private $adherentRepository;
    private $candidacyInvitationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        AdherentRepository $adherentRepository,
        CommitteeCandidacyInvitationRepository $candidacyInvitationRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->adherentRepository = $adherentRepository;
        $this->candidacyInvitationRepository = $candidacyInvitationRepository;
    }

    public function updateCandidature(
        CommitteeCandidacy $candidacy,
        ?Adherent $adherent = null,
        ?Committee $committee = null,
    ): void {
        // if UPDATE
        if ($candidacy->getId()) {
            $candidacy->syncWithOtherCandidacies();

            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new BaseCandidacyEvent($candidacy), VotingPlatformEvents::CANDIDACY_UPDATED);

            return;
        }

        // if CREATION
        $membership = $adherent->getMembershipFor($committee);
        $membership->addCommitteeCandidacy($candidacy);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(
            new CommitteeCandidacyEvent(
                $candidacy,
                $committee,
                $adherent,
                $this->adherentRepository->findCommitteeSupervisors($committee)
            ),
            VotingPlatformEvents::CANDIDACY_CREATED
        );
    }

    public function getCandidacy(
        Adherent $adherent,
        Committee $committee,
        bool $createIfNotExist = false,
    ): ?CommitteeCandidacy {
        $membership = $adherent->getMembershipFor($committee);

        $candidacy = $membership->getCommitteeCandidacy($election = $committee->getCommitteeElection());

        if ($createIfNotExist && !$candidacy && $election) {
            return $this->createCandidacy($election, $adherent->getGender());
        }

        return $candidacy;
    }

    public function createCandidacy(CommitteeElection $election, string $adherentGender): CandidacyInterface
    {
        return new CommitteeCandidacy($election, $adherentGender);
    }

    public function removeCandidacy(Adherent $adherent, Committee $committee): void
    {
        $committeeElection = $committee->getCommitteeElection();
        $membership = $adherent->getMembershipFor($committee);

        if ($membership && $candidacy = $membership->getCommitteeCandidacy($committeeElection)) {
            $invitations = $candidacy->getInvitations();

            $this->entityManager->remove($candidacy);
            $this->entityManager->flush();

            if ($invitations) {
                $this->eventDispatcher->dispatch(
                    new CandidacyInvitationEvent($candidacy, null, $invitations),
                    VotingPlatformEvents::CANDIDACY_INVITATION_REMOVE
                );
            }

            $this->eventDispatcher->dispatch(
                new CommitteeCandidacyEvent(
                    $candidacy,
                    $committee,
                    $adherent,
                    $this->adherentRepository->findCommitteeSupervisors($committee)
                ),
                VotingPlatformEvents::CANDIDACY_REMOVED
            );
        }
    }

    public function updateInvitation(
        CommitteeCandidacyInvitation $invitation,
        CommitteeCandidacy $candidacy,
        ?CommitteeMembership $previouslyInvitedMembership = null,
    ): void {
        $invitation->resetStatus();

        $this->updateCandidature($candidacy);

        $this->eventDispatcher->dispatch(
            new CandidacyInvitationEvent($candidacy, null, [$invitation], $previouslyInvitedMembership ? [$previouslyInvitedMembership] : []),
            VotingPlatformEvents::CANDIDACY_INVITATION_UPDATE
        );
    }

    public function acceptInvitation(CommitteeCandidacyInvitation $invitation, CommitteeCandidacy $acceptedBy): void
    {
        $invitation->accept();

        $invitedBy = $invitation->getCandidacy();
        $invitedBy->candidateWith($acceptedBy);

        $acceptedBy->syncWithOtherCandidacies();

        $invitedBy->confirm();
        $acceptedBy->confirm();

        $membership = $invitation->getMembership();

        $this->updateCandidature($acceptedBy, $membership->getAdherent(), $membership->getCommittee());

        $this->eventDispatcher->dispatch(
            new CandidacyInvitationEvent($invitation->getCandidacy(), $acceptedBy, [$invitation]),
            VotingPlatformEvents::CANDIDACY_INVITATION_ACCEPT
        );
    }

    public function declineInvitation(CommitteeCandidacyInvitation $invitation): void
    {
        $invitation->decline();

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(
            new CandidacyInvitationEvent($invitation->getCandidacy(), null, [$invitation]),
            VotingPlatformEvents::CANDIDACY_INVITATION_DECLINE
        );
    }

    /**
     * @return CandidacyInvitationInterface[]
     */
    public function getInvitationsToDecline(CommitteeMembership $membership, CommitteeElection $election): array
    {
        return $this->candidacyInvitationRepository->findAllPendingForMembership($membership, $election);
    }
}
