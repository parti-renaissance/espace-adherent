<?php

namespace AppBundle\Election;

use AppBundle\Entity\Election\CityVoteResult;
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
}
