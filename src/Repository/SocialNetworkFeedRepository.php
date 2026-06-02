<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SocialNetwork\SocialNetworkFeed;
use App\Entity\SocialNetwork\SocialNetworkFeedPhoto;
use App\Entity\SocialNetwork\SocialNetworkFeedVideo;
use App\Entity\VideoStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SocialNetworkFeedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialNetworkFeed::class);
    }

    public function findOneByScraperId(int $scraperId): ?SocialNetworkFeed
    {
        return $this->findOneBy(['scraperId' => $scraperId]);
    }

    /**
     * @return SocialNetworkFeed[]
     */
    public function findUnpublished(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->where('f.published = :published')
            ->setParameter('published', false)
            ->orderBy('f.id', 'ASC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Number of photos that have a source but have not been copied to our bucket yet.
     * Committed read so it stays race-safe across the parallel copy handlers.
     */
    public function countUncopiedPhotos(SocialNetworkFeed $feed): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(SocialNetworkFeedPhoto::class, 'p')
            ->where('p.feed = :feed')
            ->andWhere('p.src IS NOT NULL')
            ->andWhere('p.publicSrc IS NULL')
            ->setParameter('feed', $feed)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Number of videos that were submitted for transcoding (have a stream URL) but are not READY yet.
     */
    public function countUntranscodedVideos(SocialNetworkFeed $feed): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return (int) $qb
            ->select('COUNT(v.id)')
            ->from(SocialNetworkFeedVideo::class, 'v')
            ->leftJoin('v.video', 'vid')
            ->where('v.feed = :feed')
            ->andWhere('v.streamUrl IS NOT NULL')
            ->andWhere($qb->expr()->orX('vid IS NULL', 'vid.status != :ready'))
            ->setParameter('feed', $feed)
            ->setParameter('ready', VideoStatusEnum::READY->value)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
