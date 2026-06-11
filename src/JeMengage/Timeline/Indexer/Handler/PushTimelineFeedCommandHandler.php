<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer\Handler;

use App\JeMengage\Timeline\Indexer\IndexerClient;
use App\JeMengage\Timeline\Indexer\IndexerPayloadFactory;
use App\JeMengage\Timeline\Indexer\Message\PushTimelineFeedCommand;
use App\Repository\Timeline\TimelineFeedRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Reads the timeline_feed row at handle time and pushes its projection to the external indexer.
 *
 * Re-reading the row (rather than carrying a snapshot) is the auto-healing source of truth: a
 * delivery converges to the latest committed state. The factory returns null for a non-pushable
 * type or a leak-guarded item, in which case nothing is pushed. A row that is absent OR hidden
 * (findOnePublishableByUuid) is a no-op — the read guard prevents pushing a hidden item, and the
 * indexer has no delete endpoint in v1.
 */
#[AsMessageHandler]
class PushTimelineFeedCommandHandler
{
    public function __construct(
        private readonly TimelineFeedRepository $repository,
        private readonly IndexerPayloadFactory $payloadFactory,
        private readonly IndexerClient $client,
    ) {
    }

    public function __invoke(PushTimelineFeedCommand $command): void
    {
        $row = $this->repository->findOnePublishableByUuid($command->getUuid());

        if (null === $row) {
            return;
        }

        $payload = $this->payloadFactory->create($row);

        if (null === $payload) {
            return;
        }

        $this->client->index($payload);
    }
}
