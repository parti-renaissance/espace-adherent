<?php

namespace AppBundle\Twig;

use AppBundle\Election\CityResultAggregator;
use AppBundle\Election\CityResults;
use AppBundle\Election\ElectionManager;
use AppBundle\Entity\City;
use AppBundle\Entity\Election\CityVoteResult;
use AppBundle\Entity\Election\MinistryVoteResult;
use Twig\Extension\RuntimeExtensionInterface;

class ElectionVoteResultRuntime implements RuntimeExtensionInterface
{
    private $electionManager;
    private $cityResultsAggregator;

    public function __construct(ElectionManager $electionManager, CityResultAggregator $cityResultAggregator)
    {
        $this->electionManager = $electionManager;
        $this->cityResultsAggregator = $cityResultAggregator;
    }

    public function getCityVoteResult(City $city): ?CityVoteResult
    {
        return $this->electionManager->getCityVoteResultForCurrentElectionRound($city);
    }

    public function getMinistryVoteResult(City $city): ?MinistryVoteResult
    {
        return $this->electionManager->getMinistryVoteResultForCurrentElectionRound($city);
    }

    public function getAggregatedCityResults(City $city): CityResults
    {
        return $this->cityResultsAggregator->getResults($city);
    }
}
