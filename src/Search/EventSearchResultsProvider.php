<?php

namespace App\Search;

use App\Repository\EventRepository;

class EventSearchResultsProvider implements SearchResultsProviderInterface
{
    private $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
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
