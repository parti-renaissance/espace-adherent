<?php

declare(strict_types=1);

namespace App\Repository\Timeline;

use App\Entity\Timeline\TimelineFeed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TimelineFeedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimelineFeed::class);
    }

    /**
     * Loads the mirror rows for the given source UUIDs. findBy binds the values and applies the `uuid`
     * column type (string -> Uuid), so callers pass RFC 4122 strings. The IN clause does not preserve
     * order: the caller (IndexerTimelineProvider) re-orders the results to honour the indexer ranking.
     *
     * @param string[] $uuids RFC 4122 strings, already validated upstream
     *
     * @return TimelineFeed[]
     */
    public function findByUuids(array $uuids): array
    {
        if (!$uuids) {
            return [];
        }

        return $this->findBy(['uuid' => $uuids]);
    }
}
