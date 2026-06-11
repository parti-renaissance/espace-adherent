<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline;

use Algolia\SearchBundle\SearchService;
use App\Entity\Adherent;
use App\Entity\Algolia\AlgoliaJeMengageTimelineFeed;
use App\Repository\Timeline\TimelineHiddenFeedRepository;

class DataProvider
{
    public function __construct(
        private readonly SearchService $search,
        private readonly FeedProcessorPipeline $pipeline,
        private readonly TimelineHiddenFeedRepository $hiddenRepository,
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

        $hidden = $this->hiddenRepository->findHiddenUuids(array_column($timelineFeeds['hits'], 'objectID'));
        if ($hidden) {
            $timelineFeeds['hits'] = array_values(array_filter(
                $timelineFeeds['hits'],
                static fn (array $hit): bool => !\in_array($hit['objectID'] ?? null, $hidden, true),
            ));
        }

        $timelineFeeds['hits'] = $this->pipeline->process($user, $timelineFeeds['hits']);

        if (isset($timelineFeeds['params'])) {
            unset($timelineFeeds['params']);
        }

        return $timelineFeeds;
    }
}
