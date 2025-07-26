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
use App\VotingPlatform\Election\ResultCalculator\ResultCalculatorInterface;

class ResultCalculator
{
    /** @param ResultCalculatorInterface[]|iterable $calculators */
    public function __construct(
        private readonly iterable $calculators,
        private readonly VoterRepository $voterRepository,
        private readonly VoteResultRepository $voteResultRepository,
    ) {
    }

    public function computeElectionResult(Election $election): ElectionResult
    {
        $calculator = $this->findCalculator($election);

        if (!$electionResult = $election->getElectionResult()) {
            $electionResult = new ElectionResult($election);
            $electionResult->setParticipated($this->voterRepository->countForElection($election));

            $election->setElectionResult($electionResult);
        }

        if ($election->isClosed()) {
            $rounds = $election->getElectionRounds();
        } else {
            $rounds = [$election->getCurrentRound()];
        }

        foreach ($rounds as $currentRound) {
            if ($electionResult->alreadyFilledForRound($currentRound)) {
                continue;
            }

            $electionRoundResult = $this->createElectionRoundResultObject($currentRound);
            $electionResult->addElectionRoundResult($electionRoundResult);

            $voteResults = $this->voteResultRepository->getResultsForRound($currentRound);

            foreach ($voteResults as $voteResult) {
                $electionRoundResult->updateFromNewVoteResult($voteResult);
            }

            $calculator->calculate($electionRoundResult);
        }

        return $electionResult;
    }

    private function createElectionRoundResultObject(ElectionRound $electionRound): ElectionRoundResult
    {
        $electionRoundResult = new ElectionRoundResult($electionRound);

        foreach ($electionRound->getElectionPools() as $pool) {
            if ($pool->isSeparator) {
                continue;
            }

            $electionRoundResult->addElectionPoolResult($poolResult = new ElectionPoolResult($pool));

            foreach ($pool->getCandidateGroups() as $candidateGroup) {
                $poolResult->addCandidateGroupResult(new CandidateGroupResult($candidateGroup));
            }
        }

        return $electionRoundResult;
    }

    private function findCalculator(Election $election): ResultCalculatorInterface
    {
        $designation = $election->getDesignation();

        foreach ($this->calculators as $calculator) {
            if ($calculator->support($designation)) {
                return $calculator;
            }
        }

        throw new \RuntimeException('ResultCalculator missing');
    }
}
