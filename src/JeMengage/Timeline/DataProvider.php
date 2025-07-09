<?php

namespace App\JeMengage\Timeline;

use Algolia\SearchBundle\SearchService;
use App\Entity\Adherent;
use App\Entity\Algolia\AlgoliaJeMengageTimelineFeed;
use App\JeMengage\Timeline\FeedProcessor\FeedProcessorInterface;

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
            'filters' => implode(' AND ', $filters),
            'tagFilters' => $tagFilters,
        ]);

        $timelineFeeds['hits'] = $this->processItems($user, $timelineFeeds['hits']);

        if (isset($timelineFeeds['params'])) {
            unset($timelineFeeds['params']);
        }

        return $timelineFeeds;
    }

    private function processItems(Adherent $user, array $items): array
    {
        foreach ($items as &$item) {
            foreach ($this->processors as $processor) {
                /** @var FeedProcessorInterface $processor */
                if ($processor->supports($item, $user)) {
                    $item = $processor->process($item, $user);
                }
            }
        }

        return $items;
    }
}
