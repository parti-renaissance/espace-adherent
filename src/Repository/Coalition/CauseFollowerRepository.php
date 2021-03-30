<?php

namespace App\Repository\Coalition;

use App\Entity\Coalition\CauseFollower;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CauseFollowerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CauseFollower::class);
    }
}
