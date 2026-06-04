<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror\Handler;

use App\JeMengage\Timeline\Indexer\IndexerKind;
use App\JeMengage\Timeline\Indexer\Message\PushTimelineFeedCommand;
use App\JeMengage\Timeline\Mirror\Message\UpsertTimelineFeedCommand;
use App\JeMengage\Timeline\Mirror\TimelineFeedResolver;
use App\JeMengage\Timeline\Mirror\TimelineFeedWriter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DispatchAfterCurrentBusStamp;

#[AsMessageHandler]
class UpsertTimelineFeedCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TimelineFeedResolver $resolver,
        private readonly TimelineFeedWriter $writer,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(UpsertTimelineFeedCommand $command): void
    {
        $entity = $this->entityManager->find($command->entityClass, $command->entityId);

        if (null === $entity) {
            return;
        }

        $document = $this->resolver->resolve($entity);

        if (null === $document) {
            return;
        }

        if ($document->isRemoval()) {
            // Entity exists but is no longer indexable: remove its mirror row.
            $this->writer->delete($document->objectId);

            return;
        }

        $this->writer->upsert($document);

        // Push the row to the external indexer only for pushable types. DispatchAfterCurrentBusStamp
        // runs the push after this handler completes, so an indexer HTTP failure can never roll back
        // the mirror write (and, in sync transport, is never nested inside this handler).
        if (null !== IndexerKind::fromInternalType($document->type)) {
            $this->bus->dispatch(new Envelope(new PushTimelineFeedCommand($document->objectId))->with(new DispatchAfterCurrentBusStamp()));
        }
    }
}
