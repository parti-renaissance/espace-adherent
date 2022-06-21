<?php

namespace App\Twig;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Instance\NationalCouncil\Election as NationalCouncilElection;
use App\Entity\TerritorialCouncil\Election as TerritorialCouncilElection;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use App\Repository\CommitteeRepository;
use App\Repository\VotingPlatform\DesignationRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoteRepository;
use App\Repository\VotingPlatform\VoteResultRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Twig\Extension\RuntimeExtensionInterface;

class VotingPlatformRuntime implements RuntimeExtensionInterface
{
    private ElectionRepository $electionRepository;
    private VoteRepository $voteRepository;
    private VoteResultRepository $voteResultRepository;
    private CommitteeRepository $committeeRepository;
    private DesignationRepository $designationRepository;

    public function __construct(
        ElectionRepository $electionRepository,
        VoteRepository $voteRepository,
        VoteResultRepository $voteResultRepository,
        CommitteeRepository $committeeRepository,
        DesignationRepository $designationRepository
    ) {
        $this->electionRepository = $electionRepository;
        $this->voteRepository = $voteRepository;
        $this->voteResultRepository = $voteResultRepository;
        $this->committeeRepository = $committeeRepository;
        $this->designationRepository = $designationRepository;
    }

    public function findActivePollDesignation(): ?Designation
    {
        return current($this->designationRepository->getDesignations([DesignationTypeEnum::POLL], 1)) ?: null;
    }

    public function findElectionForCommittee(Committee $committee): ?Election
    {
        if ($committeeElection = $committee->getCommitteeElection()) {
            return $this->electionRepository->findOneForCommittee($committee, $committeeElection->getDesignation(), true);
        }

        return null;
    }

    public function findElectionForTerritorialCouncilElection(TerritorialCouncilElection $coTerrElection): ?Election
    {
        return $this->electionRepository->findOneForTerritorialCouncil($coTerrElection->getTerritorialCouncil(), $coTerrElection->getDesignation());
    }

    public function findElectionForNationalCouncilElection(NationalCouncilElection $nationalCouncilElection): ?Election
    {
        return $this->findElectionForDesignation($nationalCouncilElection->getDesignation());
    }

    public function findElectionForDesignation(Designation $designation): ?Election
    {
        return $this->electionRepository->findByDesignation($designation);
    }

    public function findMyVoteForElection(Adherent $adherent, ElectionRound $electionRound): ?Vote
    {
        return $this->voteRepository->findVote($adherent, $electionRound);
    }

    public function hasVotedForDesignation(Adherent $adherent, Designation $designation): bool
    {
        return (bool) $this->voteRepository->findVoteForDesignation($adherent, $designation);
    }

    public function getElectionParticipationDetails(ElectionRound $electionRound): array
    {
        return $this->electionRepository->getSingleAggregatedData($electionRound);
    }

    public function getElectionCandidateResult(
        int $adherentId,
        int $designationId,
        int $committeeId = null,
        int $territorialCouncilId = null
    ): array {
        return $this->voteResultRepository->getResultsForCandidate($adherentId, $designationId, $committeeId, $territorialCouncilId);
    }

    public function findCommitteeForRecentCandidate(Designation $designation, Adherent $adherent): ?Committee
    {
        return $this->committeeRepository->findCommitteeForRecentCandidate($designation, $adherent);
    }

    public function findCommitteeForRecentVote(Designation $designation, Adherent $adherent): ?Committee
    {
        return $this->committeeRepository->findCommitteeForRecentVote($designation, $adherent);
    }
}
