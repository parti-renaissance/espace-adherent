<?php

declare(strict_types=1);

namespace App\Repository\Timeline;

use App\Entity\Timeline\TimelineHiddenFeed;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class TimelineHiddenFeedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimelineHiddenFeed::class);
    }

    /**
     * Among the given UUIDs, returns those that are hidden (RFC 4122 strings). Used to post-filter
     * Algolia hits at read time, whose objectID is the source UUID.
     *
     * @param string[] $uuids
     *
     * @return string[]
     */
    public function findHiddenUuids(array $uuids): array
    {
        if (!$uuids) {
            return [];
        }

        $rows = $this->createQueryBuilder('h')
            ->select('h.uuid')
            ->andWhere('h.uuid IN (:uuids)')
            ->setParameter('uuids', $uuids)
            ->getQuery()
            ->getScalarResult()
        ;

        return array_map(static fn (array $row): string => (string) $row['uuid'], $rows);
    }

    public function isHidden(Uuid $uuid): bool
    {
        return $this->count(['uuid' => $uuid]) > 0;
    }
}
