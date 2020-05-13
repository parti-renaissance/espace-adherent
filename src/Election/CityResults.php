<?php

namespace App\Election;

use App\Entity\Election\CityVoteResult;
use App\Entity\Election\ListTotalResult as ListTotalResultEntity;
use App\Entity\Election\MinistryListTotalResult;
use App\Entity\Election\MinistryVoteResult;
use App\Entity\Election\VotePlaceResult;

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

    private $lists;

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

    public function hasResults(): bool
    {
        return $this->ministryVoteResult instanceof MinistryVoteResult
            || $this->cityVoteResult instanceof CityVoteResult
            || !empty($this->votePlacesResults)
        ;
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

    public function getTotals(): array
    {
        if ($this->isMinistryResult()) {
            return [
                'registered' => $this->ministryVoteResult->getRegistered(),
                'abstentions' => $this->ministryVoteResult->getAbstentions(),
                'participated' => $this->ministryVoteResult->getParticipated(),
                'expressed' => $this->ministryVoteResult->getExpressed(),
                'updatedAt' => $this->ministryVoteResult->getUpdatedAt(),
                'updatedBy' => $this->ministryVoteResult->getUpdatedBy(),
            ];
        }

        if ($this->isCityResult()) {
            return [
                'registered' => $this->cityVoteResult->getRegistered(),
                'abstentions' => $this->cityVoteResult->getAbstentions(),
                'participated' => $this->cityVoteResult->getParticipated(),
                'expressed' => $this->cityVoteResult->getExpressed(),
                'updatedAt' => $this->cityVoteResult->getUpdatedAt(),
                'updatedBy' => $this->cityVoteResult->getUpdatedBy(),
            ];
        }

        $registered = 0;
        $abstentions = 0;
        $participated = 0;
        $expressed = 0;
        $updatedAt = null;
        $updatedBy = null;

        foreach ($this->votePlacesResults as $votePlaceResult) {
            $registered += $votePlaceResult->getRegistered();
            $abstentions += $votePlaceResult->getAbstentions();
            $participated += $votePlaceResult->getParticipated();
            $expressed += $votePlaceResult->getExpressed();

            if ($updatedAt <= $votePlaceResult->getUpdatedAt()) {
                $updatedAt = $votePlaceResult->getUpdatedAt();
                $updatedBy = $votePlaceResult->getUpdatedBy();
            }
        }

        return [
            'registered' => $registered,
            'abstentions' => $abstentions,
            'participated' => $participated,
            'expressed' => $expressed,
            'updatedAt' => $updatedAt,
            'updatedBy' => $updatedBy,
        ];
    }

    public function getLists(): array
    {
        if (null === $this->lists) {
            if ($this->isMinistryResult()) {
                $this->lists = $this->getMinistryLists();
            } elseif ($this->isCityResult()) {
                $this->lists = $this->getCityLists();
            } else {
                $this->lists = $this->getVotePlacesLists();
            }
        }

        return $this->lists;
    }

    public function getList(string $nuance): ?array
    {
        foreach ($this->getLists() as $list) {
            if ($nuance === $list['nuance']) {
                return $list;
            }
        }

        return null;
    }

    public function getMinistryLists(): array
    {
        return $this->getListsStats(array_map(
            static function (MinistryListTotalResult $list) {
                return [
                    'name' => $list->getLabel(),
                    'nuance' => $list->getNuance(),
                    'position' => $list->getPosition(),
                    'candidate' => $list->getCandidateFirstName().' '.$list->getCandidateLastName(),
                    'outgoing_mayor' => $list->isOutgoingMayor(),
                    'total' => $list->getTotal(),
                ];
            },
            $this->ministryVoteResult->getListTotalResults()
        ));
    }

    public function getCityLists(): array
    {
        return $this->getListsStats(array_map(
            static function (ListTotalResultEntity $list) {
                return [
                    'name' => $list->getList()->getLabel(),
                    'nuance' => $list->getList()->getNuance(),
                    'position' => $list->getList()->getPosition(),
                    'candidate' => $list->getList()->getCandidateFirstName().' '.$list->getList()->getCandidateLastName(),
                    'outgoing_mayor' => $list->getList()->isOutgoingMayor(),
                    'total' => $list->getTotal(),
                ];
            },
            $this->cityVoteResult->getListTotalResults()
        ));
    }

    public function getVotePlacesLists(): array
    {
        return $this->getListsStats(array_map(
            static function (ListTotalResult $list) {
                return [
                    'name' => $list->getList()->getLabel(),
                    'nuance' => $list->getList()->getNuance(),
                    'position' => $list->getList()->getPosition(),
                    'candidate' => $list->getList()->getCandidateFirstName().' '.$list->getList()->getCandidateLastName(),
                    'outgoing_mayor' => $list->getList()->isOutgoingMayor(),
                    'total' => $list->getTotal(),
                ];
            },
            $this->getAggregatedVotePlacesResult()->getAggregatedListTotalResults()
        ));
    }

    private function getListsStats(array $lists): array
    {
        $total = 0;
        foreach ($lists as $list) {
            $total += $list['total'];
        }

        foreach ($lists as $index => $list) {
            $lists[$index]['percent'] = $total > 0 ? round(($list['total'] / $total) * 100, 2) : 0;
        }

        usort($lists, function (array $list1, array $list2) {
            return (int) $list2['total'] - (int) $list1['total'];
        });

        foreach ($lists as $place => $list) {
            $lists[$place]['place'] = $place + 1;
        }

        return $lists;
    }
}
