<?php

namespace App\Election;

use App\Entity\City;
use App\Repository\Election\CityVoteResultRepository;
use App\Repository\Election\MinistryVoteResultRepository;
use App\Repository\Election\VotePlaceResultRepository;

class CityResultAggregator
{
    private $electionManager;
    private $ministryVoteResultRepository;
    private $cityVoteResultRepository;
    private $votePlaceResultRepository;

    public function __construct(
        ElectionManager $electionManager,
        MinistryVoteResultRepository $ministryVoteResultRepository,
        CityVoteResultRepository $cityVoteResultRepository,
        VotePlaceResultRepository $votePlaceResultRepository
    ) {
        $this->electionManager = $electionManager;
        $this->ministryVoteResultRepository = $ministryVoteResultRepository;
        $this->cityVoteResultRepository = $cityVoteResultRepository;
        $this->votePlaceResultRepository = $votePlaceResultRepository;
    }

    public function getResults(City $city, bool $firstAvailableOnly = true): CityResults
    {
        $round = $this->electionManager->getClosestElectionRound();

        $ministryVoteResult = $this->ministryVoteResultRepository->findOneForCity($city, $round, true);

        if ($firstAvailableOnly && $ministryVoteResult) {
            return new CityResults($ministryVoteResult, null, []);
        }

        $cityVoteResult = $this->cityVoteResultRepository->findOneForCity($city, $round);

        if ($firstAvailableOnly && $cityVoteResult) {
            return new CityResults($ministryVoteResult, $cityVoteResult, []);
        }

        $votePlacesResults = $this->votePlaceResultRepository->findAllForCity($city, $round);

        return new CityResults($ministryVoteResult, $cityVoteResult, $votePlacesResults);
    }
}
