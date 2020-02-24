<?php

namespace AppBundle\Election;

use AppBundle\Entity\ElectionRound;
use AppBundle\Entity\VotePlace;
use AppBundle\Entity\VoteResult;
use AppBundle\Repository\ElectionRepository;
use AppBundle\Repository\VoteResultRepository;

class ElectionManager
{
    private $electionRepository;
    private $voteResultRepository;

    public function __construct(ElectionRepository $electionRepository, VoteResultRepository $voteResultRepository)
    {
        $this->electionRepository = $electionRepository;
        $this->voteResultRepository = $voteResultRepository;
    }

    public function getCurrentElectionRound(): ?ElectionRound
    {
        if (!$election = $this->electionRepository->findComingNextElection()) {
            return null;
        }

        $now = new \DateTime();

        $selectedRound = $election->getRounds()->current();
        $days = $selectedRound->getDate()->diff($now)->days;

        foreach ($election->getRounds() as $round) {
            if (($tmp = $round->getDate()->diff($now)->days) < $days) {
                $selectedRound = $round;
                $days = $tmp;
            }
        }

        return $selectedRound;
    }

    public function getVoteResultForCurrentElectionRound(VotePlace $votePlace): ?VoteResult
    {
        if (!$round = $this->getCurrentElectionRound()) {
            return null;
        }

        return $this->voteResultRepository->findOneForVotePlace($votePlace, $round) ?? new VoteResult($votePlace, $round);
    }
}
