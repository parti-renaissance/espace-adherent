<?php

declare(strict_types=1);

namespace App\VotingPlatform\Election;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Voter;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoterRepository;
use Doctrine\ORM\EntityManagerInterface;

class VotersListManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VoterRepository $voterRepository,
        private readonly ElectionRepository $electionRepository,
    ) {
    }

    public function addToElection(Election $election, Adherent $adherent): Voter
    {
        $list = $election->getVotersList();

        $voter = $this->voterRepository->findForAdherent($adherent) ?? new Voter($adherent);

        if (!$this->voterRepository->existsForElection($adherent, $election->getUuid()->toString())) {
            $list->addVoter($voter);

            $this->entityManager->persist($voter);
            $this->entityManager->flush();
        }

        return $voter;
    }

    public function removeFromCommitteeElection(Adherent $adherent, Committee $committee): void
    {
        if (!$designation = $committee->getCurrentDesignation()) {
            return;
        }

        if (!$election = $this->electionRepository->findOneForCommittee($committee, $designation)) {
            return;
        }

        if (!$election->isOpen() || $election->isVotePeriodActive()) {
            return;
        }

        if (!$voter = $this->voterRepository->findForAdherent($adherent)) {
            return;
        }

        if (!$list = $election->getVotersList()) {
            return;
        }

        $list->removeVoter($voter);
        $this->entityManager->flush();
    }

    public function addToCommitteeElection(Adherent $adherent, Committee $committee): void
    {
        if (!$designation = $committee->getCurrentDesignation()) {
            return;
        }

        if (!$election = $this->electionRepository->findOneForCommittee($committee, $designation)) {
            return;
        }

        if (!$election->isOpen() || $election->isVotePeriodActive()) {
            return;
        }

        $this->addToElection($election, $adherent);
    }
}
