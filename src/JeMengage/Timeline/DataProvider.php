<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline;

use Algolia\SearchBundle\SearchService;
use App\Entity\Adherent;
use App\Entity\AgoraMembership;
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

        $timelineFeeds['hits'] = $this->filterRestrictedInstances(
            $timelineFeeds['hits'],
            $user->getCommitteeMembership()?->getCommitteeUuid()->toRfc4122(),
            array_map(
                static fn (AgoraMembership $membership): string => $membership->agora->getUuid()->toRfc4122(),
                $user->agoraMemberships->toArray(),
            ),
        );

        $timelineFeeds['hits'] = $this->pipeline->process($user, $timelineFeeds['hits']);

        if (isset($timelineFeeds['params'])) {
            unset($timelineFeeds['params']);
        }

        return $timelineFeeds;
    }

    /**
     * @param array<int, array<string, mixed>> $hits
     * @param string[]                         $agoraUuids
     *
     * @return array<int, array<string, mixed>>
     */
    private function filterRestrictedInstances(array $hits, ?string $committeeUuid, array $agoraUuids): array
    {
        return array_values(array_filter($hits, static function (array $hit) use ($committeeUuid, $agoraUuids): bool {
            if (($committee = $hit['committee_uuid'] ?? null) && $committee !== $committeeUuid) {
                return false;
            }

            if (($agora = $hit['agora_uuid'] ?? null) && !\in_array($agora, $agoraUuids, true)) {
                return false;
            }

            return true;
        }));
    }
}
