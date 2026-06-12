<?php

declare(strict_types=1);

namespace App\Repository\Timeline;

use App\Entity\Timeline\TimelineFeed;
use App\Entity\Timeline\TimelineHiddenFeed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class TimelineFeedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimelineFeed::class);
    }

    /**
     * Loads the mirror rows for the given source UUIDs, excluding the ones hidden in hidden_timeline_feed.
     * The IN clause does not preserve order: the caller (IndexerTimelineProvider) re-orders the results
     * to honour the indexer ranking. Filtering here is the read-side hide guard — the external indexer
     * cannot be purged, so a hidden item must be dropped at read time.
     *
     * @param string[] $uuids RFC 4122 strings, already validated upstream
     *
     * @return TimelineFeed[]
     */
    public function findPublishableByUuids(array $uuids): array
    {
        if (!$uuids) {
            return [];
        }

        return $this->createQueryBuilder('tf')
            ->andWhere('tf.uuid IN (:uuids)')
            ->andWhere('tf.uuid NOT IN (SELECT h.uuid FROM '.TimelineHiddenFeed::class.' h)')
            ->setParameter('uuids', $uuids)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * One keyset page of candidate rows for the local audience filtering, newest first.
     * Array hydration on purpose: up to the finder scan cap rows may flow through per request — no
     * partial entities in the UnitOfWork, and the heavy `display` payload is not selected.
     *
     * @param string[] $types
     *
     * @return list<array{uuid: string, type: string, audience: ?array, publicationDate: \DateTimeImmutable, id: int}>
     */
    public function findCandidateChunk(array $types, ?\DateTimeImmutable $beforeDate, ?int $beforeId, int $limit): array
    {
        $qb = $this->createQueryBuilder('tf')
            ->select('tf.uuid', 'tf.type', 'tf.audience', 'tf.publicationDate', 'tf.id')
            ->andWhere('tf.type IN (:types)')
            ->andWhere('tf.uuid NOT IN (SELECT h.uuid FROM '.TimelineHiddenFeed::class.' h)')
            ->orderBy('tf.publicationDate', 'DESC')
            ->addOrderBy('tf.id', 'DESC')
            ->setMaxResults($limit)
            ->setParameter('types', $types);

        if (null !== $beforeDate && null !== $beforeId) {
            $qb
                ->andWhere('tf.publicationDate < :beforeDate OR (tf.publicationDate = :beforeDate AND tf.id < :beforeId)')
                ->setParameter('beforeDate', $beforeDate)
                ->setParameter('beforeId', $beforeId);
        }

        return array_map(static function (array $row): array {
            $row['uuid'] = (string) $row['uuid'];

            return $row;
        }, $qb->getQuery()->getArrayResult());
    }

    /**
     * One mirror row by source UUID, or null if absent OR hidden — the read-side hide guard for the push.
     */
    public function findOnePublishableByUuid(Uuid $uuid): ?TimelineFeed
    {
        return $this->createQueryBuilder('tf')
            ->andWhere('tf.uuid = :uuid')
            ->andWhere('tf.uuid NOT IN (SELECT h.uuid FROM '.TimelineHiddenFeed::class.' h)')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
