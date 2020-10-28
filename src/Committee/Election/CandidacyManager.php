<?php

namespace App\Committee\Election;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeCandidacyInvitation;
use App\Entity\CommitteeElection;
use App\Entity\CommitteeMembership;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Repository\CommitteeMembershipRepository;
use App\TerritorialCouncil\Events;
use App\VotingPlatform\Event\BaseCandidacyEvent;
use App\VotingPlatform\Event\CandidacyInvitationEvent;
use App\VotingPlatform\Event\CommitteeCandidacyEvent;
use App\VotingPlatform\Events as VotingPlatformEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CandidacyManager
{
    private $entityManager;
    private $eventDispatcher;
    private $committeeMembershipRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        CommitteeMembershipRepository $repository
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->committeeMembershipRepository = $repository;
    }

    public function updateCandidature(
        CommitteeCandidacy $candidacy,
        Adherent $adherent = null,
        Committee $committee = null
    ): void {
        // if UPDATE
        if ($candidacy->getId()) {
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(VotingPlatformEvents::CANDIDACY_UPDATED, new BaseCandidacyEvent($candidacy));

            return;
        }

        // if CREATION
        $membership = $adherent->getMembershipFor($committee);
        $membership->addCommitteeCandidacy($candidacy);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(
            VotingPlatformEvents::CANDIDACY_CREATED,
            new CommitteeCandidacyEvent(
                $candidacy,
                $committee,
                $adherent,
                $this->committeeMembershipRepository->findSupervisor($committee)
            )
        );
    }

    public function getCandidacy(Adherent $adherent, Committee $committee): ?CommitteeCandidacy
    {
        $membership = $adherent->getMembershipFor($committee);

        return $membership->getCommitteeCandidacy($committee->getCommitteeElection());
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
            $this->entityManager->remove($candidacy);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(
                VotingPlatformEvents::CANDIDACY_REMOVED,
                new CommitteeCandidacyEvent(
                    $candidacy,
                    $committee,
                    $adherent,
                    $this->committeeMembershipRepository->findSupervisor($committee)
                )
            );
        }
    }

    public function updateInvitation(
        CommitteeCandidacyInvitation $invitation,
        CommitteeCandidacy $candidacy,
        CommitteeMembership $previouslyInvitedMembership = null
    ): void {
        $invitation->resetStatus();

        $this->updateCandidature($candidacy);

        $this->eventDispatcher->dispatch(
            Events::CANDIDACY_INVITATION_UPDATE,
            new CandidacyInvitationEvent($candidacy, $invitation, $previouslyInvitedMembership)
        );
    }
}
