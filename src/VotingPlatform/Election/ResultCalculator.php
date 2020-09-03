<?php

namespace App\VotingPlatform\Election;

use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionResult\CandidateGroupResult;
use App\Entity\VotingPlatform\ElectionResult\ElectionPoolResult;
use App\Entity\VotingPlatform\ElectionResult\ElectionResult;
use App\Entity\VotingPlatform\ElectionResult\ElectionRoundResult;
use App\Entity\VotingPlatform\ElectionRound;
use App\Repository\VotingPlatform\VoteResultRepository;
use App\Repository\VotingPlatform\VoterRepository;

class ResultCalculator
{
    private $voterRepository;
    private $voteResultRepository;

    public function __construct(VoterRepository $voterRepository, VoteResultRepository $voteResultRepository)
    {
        $this->voterRepository = $voterRepository;
        $this->voteResultRepository = $voteResultRepository;
    }

    public function computeElectionResult(Election $election): ElectionResult
    {
        if (!$electionResult = $election->getElectionResult()) {
            $electionResult = new ElectionResult($election);
            $electionResult->setParticipated($this->voterRepository->countForElection($election));

            $election->setElectionResult($electionResult);
        }

        $currentRound = $election->getCurrentRound();

        if ($electionResult->alreadyFilledForRound($currentRound)) {
            return $electionResult;
        }

        $electionRoundResult = $this->createElectionRoundResultObject($currentRound);
        $electionResult->addElectionRoundResult($electionRoundResult);

        $voteResults = $this->voteResultRepository->getResultsForRound($currentRound);

        foreach ($voteResults as $voteResult) {
            $electionRoundResult->updateFromNewVoteResult($voteResult);
        }

        $electionRoundResult->sync();

        return $electionResult;
    }

    private function createElectionRoundResultObject(ElectionRound $electionRound): ElectionRoundResult
    {
        $electionRoundResult = new ElectionRoundResult($electionRound);

        foreach ($electionRound->getElectionPools() as $pool) {
            $electionRoundResult->addElectionPoolResult($poolResult = new ElectionPoolResult($pool));

            foreach ($pool->getCandidateGroups() as $candidateGroup) {
                $poolResult->addCandidateGroupResult(new CandidateGroupResult($candidateGroup));
            }
        }

        return $electionRoundResult;
    }
}
