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
