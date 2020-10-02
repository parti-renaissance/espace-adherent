<?php

namespace App\Twig;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\TerritorialCouncil\Election as TerritorialCouncilElection;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Entity\VotingPlatform\Vote;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoteRepository;
use Twig\Extension\RuntimeExtensionInterface;

class VotingPlatformRuntime implements RuntimeExtensionInterface
{
    private $electionRepository;
    private $voteRepository;

    public function __construct(ElectionRepository $electionRepository, VoteRepository $voteRepository)
    {
        $this->electionRepository = $electionRepository;
        $this->voteRepository = $voteRepository;
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

    public function findMyVoteForElection(Adherent $adherent, ElectionRound $electionRound): ?Vote
    {
        return $this->voteRepository->findVote($adherent, $electionRound);
    }

    public function findMyLastVote(Adherent $adherent): ?Vote
    {
        return $this->voteRepository->findLastForAdherent($adherent, new \DateTime('-3 months'));
    }

    public function getElectionParticipationDetails(ElectionRound $electionRound): array
    {
        return $this->electionRepository->getSingleAggregatedData($electionRound);
    }
}
