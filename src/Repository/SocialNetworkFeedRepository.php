<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SocialNetwork\SocialNetworkFeed;
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
}
