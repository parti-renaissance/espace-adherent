<?php

namespace AppBundle\Twig;

use AppBundle\Election\CityResultAggregator;
use AppBundle\Election\CityResults;
use AppBundle\Election\ElectionManager;
use AppBundle\Entity\City;
use AppBundle\Entity\Election\CityVoteResult;
use AppBundle\Entity\Election\MinistryListTotalResult;
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

    public function getMinistryResultsHistory(MinistryVoteResult $lastResult): array
    {
        /** @var MinistryVoteResult[] $results */
        $results = array_merge(
            $this->electionManager->getMunicipalMinistryResultsHistory($lastResult->getCity()),
            [$lastResult]
        );

        $rows = [];

        foreach ($results as $result) {
            $rows[$result->getElectionRound()->getDate()->format('d/m/Y')] = array_merge(...array_map(
                static function (MinistryListTotalResult $list) {
                    return [$list->getNuance() => $list->getTotal()];
                }, $result->getListTotalResults()
            ));
        }

        return [
            'years' => array_keys($rows),
            'nuances' => array_keys(array_merge(...array_values($rows))),
            'rows' => $rows,
        ];
    }
}
