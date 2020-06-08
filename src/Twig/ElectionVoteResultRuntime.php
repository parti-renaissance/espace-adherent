<?php

namespace App\Twig;

use App\Election\CityResultAggregator;
use App\Election\CityResults;
use App\Election\ElectionManager;
use App\Entity\City;
use App\Entity\Election\CityVoteResult;
use App\Entity\Election\MinistryListTotalResult;
use App\Entity\Election\MinistryVoteResult;
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
        return $this->cityResultsAggregator->getResults($city, false);
    }

    public function getMinistryResultsHistory(City $city): array
    {
        /** @var MinistryVoteResult[] $results */
        $results = $this->electionManager->getMunicipalMinistryResultsHistory($city);

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
