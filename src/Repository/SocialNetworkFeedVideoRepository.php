<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SocialNetwork\SocialNetworkFeedVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SocialNetworkFeedVideo>
 */
class SocialNetworkFeedVideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialNetworkFeedVideo::class);
    }
}
