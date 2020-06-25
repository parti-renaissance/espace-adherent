<?php

namespace App\Twig;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Election;
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
            return $this->electionRepository->findOneForCommittee($committee, $committeeElection->getDesignation());
        }

        return null;
    }

    public function findMyVoteForElection(Adherent $adherent, Election $election): ?Vote
    {
        return $this->voteRepository->findVote($adherent, $election->getCurrentRound());
    }

    public function findMyLastVote(Adherent $adherent): ?Vote
    {
        return $this->voteRepository->findLastForAdherent($adherent, new \DateTime('-3 months'));
    }
}
