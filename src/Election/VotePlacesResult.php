<?php

namespace AppBundle\Election;

use AppBundle\Entity\Election\VotePlaceResult;

class VotePlacesResult
{
    /**
     * @var VotePlaceResult[]|array
     */
    private $votePlacesResults;

    /**
     * @var ListTotalResult[]|array
     */
    private $aggregatedListTotalResults;

    /**
     * @param VotePlaceResult[]|array $votePlacesResults
     */
    public function __construct(array $votePlacesResults)
    {
        $this->votePlacesResults = $votePlacesResults;
        $this->aggregatedListTotalResults = $this->aggregateListTotalResults($votePlacesResults);
    }

    public function getAggregatedListTotalResults(): array
    {
        return $this->aggregatedListTotalResults;
    }

    /**
     * @param VotePlaceResult[]|array $votePlacesResults
     *
     * @return ListTotalResult[]|array
     */
    private function aggregateListTotalResults(array $votePlacesResults): array
    {
        $lists = [];

        foreach ($votePlacesResults as $votePlacesResult) {
            foreach ($votePlacesResult->getListTotalResults() as $listTotalResult) {
                $list = $listTotalResult->getList();

                if (!\array_key_exists($list->getId(), $lists)) {
                    $lists[$list->getId()] = new ListTotalResult($list, $listTotalResult->getTotal());
                } else {
                    $lists[$list->getId()]->addTotal($listTotalResult->getTotal());
                }
            }
        }

        return array_values($lists);
    }
}
