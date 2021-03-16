<?php

namespace App\Twig;

use App\Entity\TerritorialCouncil\Election;
use App\Entity\TerritorialCouncil\ElectionPoll\Poll;
use App\Entity\TerritorialCouncil\ElectionPoll\Vote;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Repository\TerritorialCouncil\CandidacyInvitationRepository;
use App\Repository\TerritorialCouncil\CandidacyRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\TerritorialCouncil\ElectionPoll\Manager as ElectionPollManager;
use Twig\Extension\RuntimeExtensionInterface;

class TerritorialCouncilRuntime implements RuntimeExtensionInterface
{
    private $manager;
    private $candidateRepository;
    private $electionRepository;
    private $candidacyInvitationRepository;

    public function __construct(
        ElectionPollManager $manager,
        CandidacyRepository $candidacyRepository,
        ElectionRepository $electionRepository,
        CandidacyInvitationRepository $candidacyInvitationRepository
    ) {
        $this->manager = $manager;
        $this->candidateRepository = $candidacyRepository;
        $this->electionRepository = $electionRepository;
        $this->candidacyInvitationRepository = $candidacyInvitationRepository;
    }

    public function getElectionPollVote(Poll $poll, TerritorialCouncilMembership $membership): ?Vote
    {
        return $this->manager->findVote($poll, $membership);
    }

    public function getCandidatesStats(Election $election): array
    {
        return $this->candidateRepository->getCandidatesStats($election);
    }

    public function getVotesStats(Election $election): array
    {
        return $this->electionRepository->getAllAggregatedDataForTerritorialCouncil($election->getTerritorialCouncil(), $election->getDesignation());
    }

    public function getAllCoterrCandidacyInvitationsForMembership(
        TerritorialCouncilMembership $membership,
        Election $election
    ): array {
        return $this->candidacyInvitationRepository->findAllPendingForMembership($membership, $election);
    }
}
