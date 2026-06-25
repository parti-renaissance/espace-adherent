<?php

declare(strict_types=1);

namespace App\Repository\Timeline;

use App\Entity\Geo\Zone;
use App\Entity\Timeline\TimelineFeed;
use App\Entity\Timeline\TimelineHiddenFeed;
use App\Event\EventVisibilityEnum;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * @return TimelineFeed[]
     */
    public function findPublicFeed(int $page, int $size, ?Zone $zone = null): array
    {
        return $this->createPublicFeedQueryBuilder($zone)
            ->orderBy('tf.publicationDate', 'DESC')
            ->setFirstResult($page * $size)
            ->setMaxResults($size)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countPublicFeed(?Zone $zone = null): int
    {
        return (int) $this->createPublicFeedQueryBuilder($zone)
            ->select('COUNT(tf.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createPublicFeedQueryBuilder(?Zone $zone): QueryBuilder
    {
        $qb = $this->createQueryBuilder('tf')
            ->andWhere('tf.type IN (:types)')
            ->setParameter('types', [
                TimelineFeedTypeEnum::SOCIAL_NETWORK_POST,
                TimelineFeedTypeEnum::EVENT,
                TimelineFeedTypeEnum::ACTION,
            ])
            ->andWhere('(tf.type != :event OR (tf.visibility = :public AND tf.committeeUuid IS NULL AND tf.agoraUuid IS NULL))')
            ->setParameter('event', TimelineFeedTypeEnum::EVENT)
            ->setParameter('public', EventVisibilityEnum::PUBLIC->value)
            ->andWhere('tf.uuid NOT IN (SELECT h.uuid FROM '.TimelineHiddenFeed::class.' h)')
        ;

        if (null !== $zone) {
            $qb
                ->andWhere('(JSON_CONTAINS(tf.display, :zoneCode, \'$.zone_codes\') = 1 OR JSON_CONTAINS(tf.display, \'true\', \'$.is_national\') = 1)')
                ->setParameter('zoneCode', json_encode($zone->getTypeCode()))
            ;
        }

        return $qb;
    }
}
