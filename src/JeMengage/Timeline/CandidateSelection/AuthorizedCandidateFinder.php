<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\CandidateSelection;

use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use App\Repository\Timeline\TimelineFeedRepository;
use Psr\Log\LoggerInterface;

/**
 * Computes the authorization boundary of the ranker read path: the set of timeline_feed UUIDs the
 * current user is allowed to see (DESIGN Decision 3), newest first. The scan is bounded and any
 * truncation is logged, never silent (DESIGN Decision 4). transactional_message is excluded from
 * the ranker path (DESIGN Decision 7: private message UUIDs are not sent to the external service).
 *
 * The caps are constructor parameters with production defaults so tests can exercise the boundaries
 * without seeding thousands of rows; autowiring uses the defaults, no service config needed.
 */
class AuthorizedCandidateFinder
{
    public const array CANDIDATE_TYPES = [
        TimelineFeedTypeEnum::NEWS,
        TimelineFeedTypeEnum::EVENT,
        TimelineFeedTypeEnum::ACTION,
        TimelineFeedTypeEnum::PUBLICATION,
        TimelineFeedTypeEnum::SOCIAL_NETWORK_POST,
    ];

    public function __construct(
        private readonly TimelineFeedRepository $repository,
        private readonly AudienceMatcher $matcher,
        private readonly LoggerInterface $logger,
        private readonly int $maxCandidates = 2000,
        private readonly int $chunkSize = 500,
        private readonly int $maxScannedRows = 10000,
    ) {
    }

    /**
     * @return string[] candidate uuids, newest first
     */
    public function findCandidateUuids(AudienceContext $context, ?TimelineRequestFilter $filter = null): array
    {
        $candidates = [];
        $scanned = 0;
        $beforeDate = null;
        $beforeId = null;

        while ([] !== $chunk = $this->repository->findCandidateChunk(self::CANDIDATE_TYPES, $beforeDate, $beforeId, $this->chunkSize)) {
            foreach ($chunk as $row) {
                ++$scanned;

                if ($this->matcher->matches($context, $row['type'], $row['audience'], $filter)) {
                    $candidates[] = $row['uuid'];

                    if (\count($candidates) >= $this->maxCandidates) {
                        $this->logger->info('Timeline candidate cap reached.', [
                            'user_id' => $context->profile->userId,
                            'candidates' => \count($candidates),
                            'scanned' => $scanned,
                        ]);

                        return $candidates;
                    }
                }

                if ($scanned >= $this->maxScannedRows) {
                    // The "sparse audience" divergence of DESIGN Decision 4: observable, never silent.
                    $this->logger->warning('Timeline candidate scan cap reached, older items skipped.', [
                        'user_id' => $context->profile->userId,
                        'candidates' => \count($candidates),
                        'scanned' => $scanned,
                    ]);

                    return $candidates;
                }
            }

            $last = end($chunk);
            $beforeDate = $last['publicationDate'];
            $beforeId = $last['id'];
        }

        return $candidates;
    }
}
