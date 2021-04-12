<?php

namespace App\Repository\Coalition;

use App\Entity\Coalition\Cause;
use App\Entity\Coalition\CauseFollower;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CauseFollowerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CauseFollower::class);
    }

    public function countForCause(Cause $cause): int
    {
        return (int) $this->createQueryBuilder('follower')
            ->select('COUNT(1)')
            ->where('follower.cause = :cause')
            ->setParameter('cause', $cause)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
