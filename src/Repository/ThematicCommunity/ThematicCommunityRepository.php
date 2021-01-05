<?php

namespace App\Repository\ThematicCommunity;

use App\Entity\ThematicCommunity\ThematicCommunity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ThematicCommunityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThematicCommunity::class);
    }
}
