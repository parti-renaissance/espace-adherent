<?php

namespace App\Search;

class SearchResultsProvidersManager
{
    /** @var SearchResultsProviderInterface[] */
    private $providers;

    /**
     * @throws \RuntimeException if no provider supports this $search
     */
    public function find(SearchParametersFilter $search): array
    {
        if (isset($this->providers[$search->getType()])) {
            return $this->providers[$search->getType()]->find($search);
        }

        throw new \RuntimeException(sprintf('No provider was able to handle the search type "%s"', $search->getType()));
    }

    /**
     * @throws \RuntimeException If one provider already supports the type of search
     */
    public function addProvider(SearchResultsProviderInterface $provider): void
    {
        if (isset($this->providers[$provider->getSupportedTypeOfSearch()])) {
            throw new \RuntimeException(sprintf('This type of search is already supported by another provider. You provided "%s" and "%s" already supports "%s" type of search', \get_class($provider), \get_class($this->providers[$provider->getSupportedTypeOfSearch()]), $provider->getSupportedTypeOfSearch()));
        }

        $this->providers[$provider->getSupportedTypeOfSearch()] = $provider;
    }
}
