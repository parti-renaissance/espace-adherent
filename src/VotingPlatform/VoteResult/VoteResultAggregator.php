<?php

namespace App\VotingPlatform\VoteResult;

use App\Command\VotingPlatformConfigureCommand;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Repository\VotingPlatform\VoteResultRepository;
use App\Repository\VotingPlatform\VoterRepository;

class VoteResultAggregator
{
    private $voteResultRepository;
    private $voterRepository;

    public function __construct(VoteResultRepository $voteResultRepository, VoterRepository $voterRepository)
    {
        $this->voteResultRepository = $voteResultRepository;
        $this->voterRepository = $voterRepository;
    }

    public function getResultsForRound(ElectionRound $electionRound): array
    {
        $voteResults = $this->voteResultRepository->getResults($electionRound);
        $participants = $this->voterRepository->countForElection($electionRound->getElection());

        $blankPoolData = $this->buildBlankPoolData($voteResults, $participants);

        $aggregated = [
            'candidates' => [],
            'resume' => [
                VotingPlatformConfigureCommand::POOL_WOMEN_TITLE => $blankPoolData,
                VotingPlatformConfigureCommand::POOL_MEN_TITLE => $blankPoolData,
            ],
        ];

        foreach ($voteResults as $voteResult) {
            foreach ($voteResult->getVoteChoices() as $index => $voteChoice) {
                if (!isset($aggregated['resume'][$poolTitle = $voteChoice->getElectionPool()->getTitle()])) {
                    $aggregated['resume'][$poolTitle] = $this->buildBlankPoolData($voteResult, $participants);
                }

                if (true === $voteChoice->isBlank()) {
                    ++$aggregated['resume'][$poolTitle]['blank'];
                } else {
                    ++$aggregated['resume'][$poolTitle]['expressed'];

                    $candidateGroupUuid = $voteChoice->getCandidateGroup()->getUuid()->toString();

                    if (!isset($aggregated['candidates'][$candidateGroupUuid])) {
                        $aggregated['candidates'][$candidateGroupUuid] = 0;
                    }

                    ++$aggregated['candidates'][$candidateGroupUuid];
                }
            }
        }

        // Sort candidates list
        arsort($aggregated['candidates']);

        return [
            'vote_results' => $voteResults,
            'aggregated' => $aggregated,
        ];
    }

    public function getResults(Election $election): array
    {
        return $this->getResultsForRound($election->getCurrentRound());
    }

    private function buildBlankPoolData(array $voteResults, int $participants): array
    {
        return [
            'blank' => 0,
            'participated' => $participants,
            'expressed' => 0,
            'abstentions' => $participants - \count($voteResults),
        ];
    }
}
