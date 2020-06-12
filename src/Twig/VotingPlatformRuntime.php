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
        return $this->electionRepository->findOneForCommittee($committee);
    }

    public function findMyVoteForElection(Adherent $adherent, Election $election): ?Vote
    {
        return $this->voteRepository->findVote($adherent, $election->getCurrentRound());
    }
}
