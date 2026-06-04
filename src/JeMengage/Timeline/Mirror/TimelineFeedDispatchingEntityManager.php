<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror;

use App\Algolia\AlgoliaIndexedEntityManager;
use App\Algolia\AlgoliaIndexerInterface;
use App\Entity\AlgoliaIndexedEntityInterface;
use App\JeMengage\Timeline\Mirror\Message\DeleteTimelineFeedCommand;
use App\JeMengage\Timeline\Mirror\Message\UpsertTimelineFeedCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Decorates the Algolia indexing choke point to additionally feed the timeline_feed mirror,
 * asynchronously, without touching the (synchronous, unchanged) Algolia write.
 *
 * On every hook it first delegates to the inner indexer (Algolia, intact) and then dispatches a
 * mirror message for the timeline source entities only. It implements AlgoliaIndexerInterface and
 * composes the decorated inner indexer — no inheritance, all behaviour comes from $inner.
 */
#[AsDecorator(AlgoliaIndexedEntityManager::class)]
class TimelineFeedDispatchingEntityManager implements AlgoliaIndexerInterface
{
    public function __construct(
        #[AutowireDecorated]
        private readonly AlgoliaIndexerInterface $inner,
        private readonly MessageBusInterface $bus,
        private readonly TimelineFeedResolver $resolver,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function postPersist(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->inner->postPersist($entity);
        $this->dispatchUpsert($entity);
    }

    public function postUpdate(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->inner->postUpdate($entity);
        $this->dispatchUpsert($entity);
    }

    public function preRemove(AlgoliaIndexedEntityInterface $entity): void
    {
        $this->inner->preRemove($entity);

        $objectId = $this->resolver->objectId($entity);
        if (null !== $objectId) {
            $this->bus->dispatch(new DeleteTimelineFeedCommand($objectId));
        }
    }

    public function batch(array $entities): void
    {
        $this->inner->batch($entities);

        foreach ($entities as $entity) {
            $this->dispatchUpsert($entity);
        }
    }

    private function dispatchUpsert(object $entity): void
    {
        if (!$this->resolver->supports($entity)) {
            return;
        }

        $metadata = $this->entityManager->getClassMetadata($entity::class);
        $identifiers = $metadata->getIdentifierValues($entity);

        $this->bus->dispatch(new UpsertTimelineFeedCommand($metadata->getName(), reset($identifiers)));
    }
}
