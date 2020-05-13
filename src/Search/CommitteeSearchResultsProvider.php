<?php

namespace App\Search;

use App\Repository\CommitteeRepository;

class CommitteeSearchResultsProvider implements SearchResultsProviderInterface
{
    private $committeeRepository;

    public function __construct(CommitteeRepository $committeeRepository)
    {
        $this->committeeRepository = $committeeRepository;
    }

    public function find(SearchParametersFilter $search): array
    {
        return $this->committeeRepository->searchCommittees($search);
    }

    public function getSupportedTypeOfSearch(): string
    {
        return SearchParametersFilter::TYPE_COMMITTEES;
    }
}
