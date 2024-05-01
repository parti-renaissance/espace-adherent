<?php

namespace App\JeMengage\Timeline;

use App\Algolia\SearchService;
use App\Entity\Adherent;
use App\Entity\Algolia\AlgoliaJeMengageTimelineFeed;
use App\JeMengage\Timeline\FeedProcessor\FeedProcessorInterface;
use App\JeMengage\Timeline\FeedProcessor\NullProcessor;

class DataProvider
{
    public function __construct(
        private readonly SearchService $search,
        private readonly iterable $processors,
    ) {
    }

    public function findItems(Adherent $user, int $page, array $filters, array $tagFilters): array
    {
        $timelineFeeds = $this->search->rawSearch(AlgoliaJeMengageTimelineFeed::class, '', [
            'page' => $page,
            'attributesToHighlight' => [],
            'filters' => implode(' OR ', $filters),
            'tagFilters' => $tagFilters,
        ]);

        $timelineFeeds['hits'] = $this->processItems($user, $timelineFeeds['hits']);

        return $timelineFeeds;
    }

    private function processItems(Adherent $user, array $items): array
    {
        $context = [];
        foreach ($items as &$item) {
            $item = $this->getProcessor($item)->process($item, $context);
        }

        return $items;
    }

    private function getProcessor(array $item): FeedProcessorInterface
    {
        /** @var FeedProcessorInterface $processor */
        foreach ($this->processors as $processor) {
            if ($processor->supports($item)) {
                return $processor;
            }
        }

        return new NullProcessor();
    }
}
