<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Mirror;

use Algolia\SearchBundle\Searchable;
use App\Entity\Algolia\AlgoliaJeMengageTimelineFeed;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Resolves, for a timeline source entity, the mirror document identical to the record the
 * Algolia bundle would index for the jemengage_timeline_feed aggregator.
 *
 * The document is never re-implemented: it is derived the same way the bundle's Engine builds
 * it — by normalizing the AlgoliaJeMengageTimelineFeed aggregator at the searchableArray format,
 * which routes to the AbstractJeMengageTimelineFeedNormalizer family.
 */
class TimelineFeedResolver
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NormalizerInterface $serializer,
        private readonly TimelineFeedTransformer $transformer,
    ) {
    }

    public function supports(object $entity): bool
    {
        return isset(TimelineFeedTypeEnum::CLASS_MAPPING[$this->realClass($entity)]);
    }

    public function objectId(object $entity): ?Uuid
    {
        if (!$this->supports($entity)) {
            return null;
        }

        return $this->objectIdOf($this->aggregatorFor($entity));
    }

    public function resolve(object $entity): ?TimelineFeedDocument
    {
        if (!$this->supports($entity)) {
            return null;
        }

        $aggregator = $this->aggregatorFor($entity);
        $objectId = $this->objectIdOf($aggregator);

        // Non-indexable (e.g. unpublished) => the mirror row must be removed.
        if (!$aggregator->isIndexable()) {
            return new TimelineFeedDocument($objectId, null, null, null, null, null);
        }

        $context = ['fieldsMapping' => $this->entityManager->getClassMetadata(AlgoliaJeMengageTimelineFeed::class)->fieldMappings];
        $document = $this->serializer->normalize($aggregator, Searchable::NORMALIZATION_FORMAT, $context);

        // The Engine skips empty documents; mirror the same behaviour.
        if (empty($document)) {
            return null;
        }

        // The display payload is exactly what Engine::index() stores: objectID (as string) merged
        // with the normalizer output. The transformer derives type/dates/audience from it.
        $record = array_merge(['objectID' => $objectId->toRfc4122()], $document);
        $canonical = $this->transformer->transform($record);

        return new TimelineFeedDocument(
            $objectId,
            $canonical['type'],
            $canonical['publicationDate'],
            $canonical['eventDate'],
            $canonical['audience'],
            $canonical['display'],
            $canonical['visibility'],
            $canonical['committeeUuid'],
            $canonical['agoraUuid'],
        );
    }

    private function aggregatorFor(object $entity): AlgoliaJeMengageTimelineFeed
    {
        $identifiers = $this->entityManager->getClassMetadata($this->realClass($entity))->getIdentifierValues($entity);

        return new AlgoliaJeMengageTimelineFeed($entity, $identifiers);
    }

    private function objectIdOf(AlgoliaJeMengageTimelineFeed $aggregator): Uuid
    {
        $identifiers = $this->entityManager->getClassMetadata(AlgoliaJeMengageTimelineFeed::class)->getIdentifierValues($aggregator);

        return Uuid::fromString((string) reset($identifiers));
    }

    private function realClass(object $entity): string
    {
        return $this->entityManager->getClassMetadata($entity::class)->getName();
    }
}
