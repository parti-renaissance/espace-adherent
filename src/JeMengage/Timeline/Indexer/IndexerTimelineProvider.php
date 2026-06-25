<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

use App\Entity\Adherent;
use App\Entity\Timeline\TimelineFeed;
use App\JeMengage\Timeline\CandidateSelection\AudienceContextFactory;
use App\JeMengage\Timeline\CandidateSelection\AuthorizedCandidateFinder;
use App\JeMengage\Timeline\CandidateSelection\TimelineRequestFilter;
use App\JeMengage\Timeline\FeedProcessorPipeline;
use App\Repository\Timeline\TimelineFeedRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Ranker read path with a local authorization boundary (DESIGN Decision 3): the candidate set is
 * computed app-side from the authenticated Adherent, sent to the external ranker (candidate_ids)
 * alongside the UserProfile, and the ranker response is CLAMPED to that same set — an id outside it
 * is never served, whatever the ranker answers. Hydration from the local mirror, the FeedProcessor
 * chain and the Algolia-shaped envelope are unchanged. Any ranker failure propagates as a
 * RuntimeException; the controller catches it and falls back to the regular Algolia feed.
 */
class IndexerTimelineProvider
{
    private const int PAGE_SIZE = 10;

    public function __construct(
        private readonly AudienceContextFactory $contextFactory,
        private readonly AuthorizedCandidateFinder $candidateFinder,
        private readonly TimelineRankerClient $rankerClient,
        private readonly TimelineFeedRepository $repository,
        private readonly FeedProcessorPipeline $pipeline,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function findItems(Adherent $user, int $page, string $sessionId, ?TimelineRequestFilter $filter = null): array
    {
        // One resolution of the user dimensions per request: the same context feeds the local
        // filtering and the ranker profile payload. The view filter restricts the candidates, so
        // the clamp enforces the filtered view even if the ranker ignores the request context.
        $context = $this->contextFactory->create($user);
        $candidates = $this->candidateFinder->findCandidateUuids($context, $filter);

        // Nothing is authorized: any ranker answer would be entirely clamped, skip the call.
        if ([] === $candidates) {
            return $this->envelope([], $page, $page + 1);
        }

        $response = $this->rankerClient->getItems($context->profile, $candidates, $sessionId);

        // Clamp: ranker-returned ids are kept in the ranker order (the ranking authority) but only
        // when they belong to the candidate set sent in this very request. Out-of-set ids are an
        // incident signal (misbehaving/compromised ranker), not a nominal case.
        $allowed = array_flip($candidates);
        $orderedIds = [];
        $clamped = [];
        foreach ($response->getExternalIds() as $externalId) {
            if (!Uuid::isValid($externalId)) {
                continue;
            }

            if (isset($allowed[$externalId])) {
                $orderedIds[] = $externalId;
            } else {
                $clamped[] = $externalId;
            }
        }

        if ([] !== $clamped) {
            $this->logger->warning('Timeline ranker returned ids outside the authorized candidate set.', [
                'user_id' => $context->profile->userId,
                'count' => \count($clamped),
                'sample' => \array_slice($clamped, 0, 5),
            ]);
        }

        // Hydrate then re-order per the ranker authority (the SQL IN clause does not preserve order);
        // a candidate without a local mirror row at hydration time is skipped silently (the repository
        // hidden guard stays as defense in depth on top of the candidate exclusion).
        $rowsByUuid = [];
        foreach ($this->repository->findPublishableByUuids($orderedIds) as $row) {
            /** @var TimelineFeed $row */
            $rowsByUuid[$row->getUuid()->toRfc4122()] = $row;
        }

        $displays = [];
        foreach ($orderedIds as $externalId) {
            if (isset($rowsByUuid[$externalId])) {
                $displays[] = $rowsByUuid[$externalId]->display;
            } else {
                $this->logger->debug('Timeline: ranked candidate without local row, skipped.', ['external_id' => $externalId]);
            }
        }

        $hits = $this->pipeline->process($user, $displays);

        // nbPages over an unknown total, keyed on the POST-clamp list: while the ranker returns
        // servable items, claim one more page (page + 2); an all-clamped batch ends the pagination
        // (page + 1) — a clamped id is never servable, retrying pages would only loop on empties.
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
