<?php

namespace AppBundle\Election;

use AppBundle\Entity\Election\CityVoteResult;
use AppBundle\Entity\Election\MinistryListTotalResult;
use AppBundle\Entity\Election\MinistryVoteResult;
use AppBundle\Entity\Election\VotePlaceResult;

class CityResults
{
    /**
     * @var MinistryVoteResult|null
     */
    private $ministryVoteResult;

    /**
     * @var CityVoteResult|null
     */
    private $cityVoteResult;

    /**
     * @var VotePlaceResult[]|array
     */
    private $votePlacesResults;

    public function __construct(
        ?MinistryVoteResult $ministryVoteResult,
        ?CityVoteResult $cityVoteResult,
        array $votePlacesResults
    ) {
        $this->ministryVoteResult = $ministryVoteResult;
        $this->cityVoteResult = $cityVoteResult;
        $this->votePlacesResults = $votePlacesResults;
    }

    public function getMinistryVoteResult(): ?MinistryVoteResult
    {
        return $this->ministryVoteResult;
    }

    public function getCityVoteResult(): ?CityVoteResult
    {
        return $this->cityVoteResult;
    }

    public function getVotePlacesResults(): array
    {
        return $this->votePlacesResults;
    }

    public function isMinistryResult(): bool
    {
        return $this->ministryVoteResult instanceof MinistryVoteResult;
    }

    public function isCityResult(): bool
    {
        return !$this->isMinistryResult()
            && $this->cityVoteResult instanceof CityVoteResult
        ;
    }

    public function isVotePlacesResults(): bool
    {
        return !$this->isMinistryResult()
            && !$this->isCityResult()
            && !empty($this->votePlacesResults)
        ;
    }

    public function getAggregatedVotePlacesResult(): VotePlacesResult
    {
        return new VotePlacesResult($this->votePlacesResults);
    }

    public function getLists(): array
    {
        if ($this->isMinistryResult()) {
            return array_map(
                static function (MinistryListTotalResult $list) {
                    return [
                        'name' => $list->getLabel(),
                        'nuance' => $list->getNuance(),
                        'total' => $list->getTotal(),
                    ];
                },
                $this->ministryVoteResult->getListTotalResults()
            );
        }

        if ($this->isCityResult()) {
            return array_map(
                static function (\AppBundle\Entity\Election\ListTotalResult $list) {
                    return [
                        'name' => $list->getList()->getLabel(),
                        'nuance' => $list->getList()->getNuance(),
                        'total' => $list->getTotal(),
                    ];
                },
                $this->cityVoteResult->getListTotalResults()
            );
        }

        return array_map(
            static function (ListTotalResult $list) {
                return [
                    'name' => $list->getList()->getLabel(),
                    'nuance' => $list->getList()->getNuance(),
                    'total' => $list->getTotal(),
                ];
            },
            $this->getAggregatedVotePlacesResult()->getAggregatedListTotalResults()
        );
    }
}
