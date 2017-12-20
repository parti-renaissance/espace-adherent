<?php

namespace AppBundle\Search;

interface SearchResultsProviderInterface
{
    public function find(SearchParametersFilter $search): array;

    public function getSupportedTypeOfSearch(): string;
}
