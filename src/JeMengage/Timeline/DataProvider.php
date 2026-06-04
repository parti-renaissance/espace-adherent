<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline;

use Algolia\SearchBundle\SearchService;
use App\Entity\Adherent;
use App\Entity\Algolia\AlgoliaJeMengageTimelineFeed;

class DataProvider
{
    public function __construct(
        private readonly SearchService $search,
        private readonly FeedProcessorPipeline $pipeline,
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

        $timelineFeeds['hits'] = $this->pipeline->process($user, $timelineFeeds['hits']);

        if (isset($timelineFeeds['params'])) {
            unset($timelineFeeds['params']);
        }

        return $timelineFeeds;
    }
}
