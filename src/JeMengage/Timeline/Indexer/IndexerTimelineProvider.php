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
 * an Algolia-shaped envelope. V1 is a single top-10 page; pages beyond the first return an empty envelope
 * so the app's infinite scroll stops. Any indexer failure propagates as a RuntimeException (mapped to a
 * 503 by the controller) — there is no Algolia fallback for canary users.
 */
class IndexerTimelineProvider
{
    private const int PAGE_SIZE = 10;
    private const int OVER_FETCH = 3;

    public function __construct(
        private readonly UserProfileFactory $profileFactory,
        private readonly TimelineRankerClient $rankerClient,
        private readonly TimelineFeedRepository $repository,
        private readonly FeedProcessorPipeline $pipeline,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function findItems(Adherent $user, int $page): array
    {
        if ($page >= 1) {
            return $this->envelope([], $page);
        }

        $response = $this->rankerClient->getItems($this->profileFactory->create($user));

        // Indexer-ranked external_ids, valid UUIDs only, over-fetched to absorb skipped orphans.
        $orderedIds = [];
        foreach ($response->getExternalIds() as $externalId) {
            if (Uuid::isValid($externalId)) {
                $orderedIds[] = $externalId;
            }
        }
        $candidateIds = \array_slice($orderedIds, 0, self::PAGE_SIZE * self::OVER_FETCH);

        // Hydrate then re-order per the indexer authority (the SQL IN clause does not preserve order);
        // an indexer item without a local mirror row (depublished/deleted) is skipped silently.
        $rowsByUuid = [];
        foreach ($this->repository->findByUuids($candidateIds) as $row) {
            /** @var TimelineFeed $row */
            $rowsByUuid[$row->getUuid()->toRfc4122()] = $row;
        }

        $displays = [];
        foreach ($candidateIds as $externalId) {
            if (isset($rowsByUuid[$externalId])) {
                $displays[] = $rowsByUuid[$externalId]->display;
            } else {
                $this->logger->debug('Canary timeline: indexer item without local row, skipped.', ['external_id' => $externalId]);
            }
        }

        $hits = \array_slice($this->pipeline->process($user, $displays), 0, self::PAGE_SIZE);

        return $this->envelope($hits, $page);
    }

    private function envelope(array $hits, int $page): array
    {
        return [
            'hits' => $hits,
            'nbHits' => \count($hits),
            'page' => $page,
            'nbPages' => 1,
            'hitsPerPage' => self::PAGE_SIZE,
        ];
    }
}
