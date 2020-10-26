<?php

namespace App\Repository\ThematicCommunity;

use App\Entity\ThematicCommunity\ThematicCommunity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ThematicCommunityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ThematicCommunity::class);
    }
}
