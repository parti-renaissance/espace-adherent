<?php

namespace App\VotingPlatform\Election\ResultCalculator;

use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\CandidateGroup;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Entity\VotingPlatform\ElectionResult\CandidateGroupResult;
use App\Entity\VotingPlatform\ElectionResult\ElectionPoolResult;
use App\VotingPlatform\Election\PartyListProportional\Model\Election;
use App\VotingPlatform\Election\PartyListProportional\Model\PartyList;
use App\VotingPlatform\Election\PartyListProportional\Processor;

class PartyListProportionalCalculator extends MajoritarianCalculator
{
    public function support(Designation $designation): bool
    {
        return $designation->isPartyListProportionalType();
    }

    public static function getPriority(): int
    {
        return 0;
    }

    protected function calculateForPool(ElectionPoolResult $electionPoolResult): ?CandidateGroup
    {
        $election = $electionPoolResult->getElectionPool()->getElection();
        $designation = $election->getDesignation();

        $seats = $designation->seats;
        $primeSeats = 0;
        $majorityPrime = $designation->majorityPrime;
        $majorityPrimeRoundSubMode = $designation->majorityPrimeRoundSupMode;

        if (!$elected = parent::calculateForPool($electionPoolResult)) {
            $this->logger->error('Plateforme de vote : élection "'.$election->getId().'" a une égalité.');

            return null;
        }

        if ($majorityPrime > 0) {
            $calculated = $seats * ($majorityPrime / 100);

            $primeSeats = (int) ($majorityPrimeRoundSubMode
                ? ceil($calculated)
                : floor($calculated)
            );
        }

        Processor::process($electionToProcess = new Election(
            $seats - $primeSeats,
            array_map(static function (CandidateGroupResult $candidateGroupResult) {
                return new PartyList(
                    (string) $candidateGroupResult->getCandidateGroup()->getId(),
                    $candidateGroupResult->getTotal()
                );
            }, $candidateGroupResults = $electionPoolResult->getCandidateGroupResults())
        ));

        if ($electionToProcess->hasFreeSeats()) {
            $this->logger->error(\sprintf('Plateforme de vote : problème de distribution de siège pour l\'élection "%d", reste %d siège(s) à distribuer', $elected->getId(), $electionToProcess->getFreeSeatsNumber()));
        }

        foreach ($electionToProcess->partyLists as $list) {
            if (0 === $list->getSeats()) {
                continue;
            }

            foreach ($candidateGroupResults as $groupResult) {
                $candidateGroup = $groupResult->getCandidateGroup();
                if ($candidateGroup->getId() == $list->identifier) {
                    if ($candidateGroup === $elected && $primeSeats) {
                        $list->increaseSeats($primeSeats);
                    }

                    array_map(function (Candidate $candidate) {
                        $candidate->setAdditionallyElected(true);
                    }, \array_slice($candidateGroup->getCandidates(), 0, $list->getSeats()));

                    break;
                }
            }
        }

        return $elected;
    }
}
