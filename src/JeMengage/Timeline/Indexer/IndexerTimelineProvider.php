<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

use App\Entity\Adherent;
use App\Entity\Timeline\TimelineFeed;
use App\JeMengage\Timeline\FeedProcessorPipeline;
use App\Repository\Timeline\TimelineFeedRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Canary read path: delegates selection and ordering to the external indexer (POST /get_items), hydrates
 * the documents from the local timeline_feed mirror, applies the existing FeedProcessor chain and returns
 * an Algolia-shaped envelope. Any indexer failure propagates as a RuntimeException; the controller catches
 * it and falls back to the regular Algolia feed, so a canary defect never breaks the timeline.
 */
class IndexerTimelineProvider
{
    private const int PAGE_SIZE = 10;

    public function __construct(
        private readonly UserProfileFactory $profileFactory,
        private readonly TimelineRankerClient $rankerClient,
        private readonly TimelineFeedRepository $repository,
        private readonly FeedProcessorPipeline $pipeline,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function findItems(Adherent $user, int $page, string $sessionId): array
    {
        $response = $this->rankerClient->getItems($this->profileFactory->create($user), $sessionId);

        // Indexer-ranked external_ids, valid UUIDs only, kept in the indexer order (the ranking authority).
        // No over-fetch/slice: the indexer de-dupes by what it returned for this session, so trimming the
        // batch here would permanently drop items it has already marked as seen.
        $orderedIds = [];
        foreach ($response->getExternalIds() as $externalId) {
            if (Uuid::isValid($externalId)) {
                $orderedIds[] = $externalId;
            }
        }

        // Hydrate then re-order per the indexer authority (the SQL IN clause does not preserve order);
        // an indexer item without a local mirror row (depublished/deleted) is skipped silently. Skipped
        // orphans yield a short page — accepted (the next scroll fills in).
        $rowsByUuid = [];
        foreach ($this->repository->findByUuids($orderedIds) as $row) {
            /** @var TimelineFeed $row */
            $rowsByUuid[$row->getUuid()->toRfc4122()] = $row;
        }

        $displays = [];
        foreach ($orderedIds as $externalId) {
            if (isset($rowsByUuid[$externalId])) {
                $displays[] = $rowsByUuid[$externalId]->display;
            } else {
                $this->logger->debug('Canary timeline: indexer item without local row, skipped.', ['external_id' => $externalId]);
            }
        }

        $hits = $this->pipeline->process($user, $displays);

        // nbPages over an unknown total: while the indexer keeps returning items, claim one more page
        // (page + 2) so the app keeps scrolling; when it returns none, this is the last page (page + 1).
        // The signal is keyed on what the indexer returned, not on the hydrated hit count — an all-orphan
        // batch yields zero hits while the stream is not exhausted.
        $nbPages = [] !== $orderedIds ? $page + 2 : $page + 1;

        return $this->envelope($hits, $page, $nbPages);
    }

    private function envelope(array $hits, int $page, int $nbPages): array
    {
        return [
            'hits' => $hits,
            'nbHits' => \count($hits),
            'page' => $page,
            'nbPages' => $nbPages,
            'hitsPerPage' => self::PAGE_SIZE,
        ];
    }
}
