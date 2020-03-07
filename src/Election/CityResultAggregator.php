<?php

namespace AppBundle\Election;

use AppBundle\Entity\City;
use AppBundle\Repository\Election\CityVoteResultRepository;
use AppBundle\Repository\Election\MinistryVoteResultRepository;
use AppBundle\Repository\Election\VotePlaceResultRepository;

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

    public function getResults(City $city): CityResults
    {
        $round = $this->electionManager->getClosestElectionRound();

        $ministryVoteResult = $this->ministryVoteResultRepository->findOneForCity($city, $round);
        $cityVoteResult = $this->cityVoteResultRepository->findOneForCity($city, $round);
        $votePlacesResults = $this->votePlaceResultRepository->findAllForCity($city, $round);

        return new CityResults($ministryVoteResult, $cityVoteResult, $votePlacesResults);
    }
}
