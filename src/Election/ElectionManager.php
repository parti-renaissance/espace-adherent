<?php

namespace AppBundle\Election;

use AppBundle\Entity\Adherent;
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

    public function getVoteResultForCurrentElectionRound(VotePlace $votePlace, Adherent $adherent): ?VoteResult
    {
        $round = $this->getCurrentElectionRound();

        $result = $this->voteResultRepository->findOneBy([
            'votePlace' => $votePlace,
            'author' => $adherent,
            'electionRound' => $round,
        ]);

        if (!$result) {
            $result = new VoteResult($votePlace, $round, $adherent);

            $result->addList('', 0);
        }

        return $result;
    }
}
