<?php

declare(strict_types=1);

namespace App\Search;

interface SearchResultsProviderInterface
{
    public function find(SearchParametersFilter $search): array;

    public function getSupportedTypeOfSearch(): string;
}
