<?php

namespace App\Search;

use App\Repository\Event\EventRepository;

class EventSearchResultsProvider implements SearchResultsProviderInterface
{
    public function __construct(private readonly EventRepository $eventRepository)
    {
    }

    public function find(SearchParametersFilter $search): array
    {
        return $this->eventRepository->searchAllEvents($search);
    }

    public function getSupportedTypeOfSearch(): string
    {
        return SearchParametersFilter::TYPE_EVENTS;
    }
}
