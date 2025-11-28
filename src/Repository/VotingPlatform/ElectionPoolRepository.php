<?php

declare(strict_types=1);

namespace App\Repository\VotingPlatform;

use App\Entity\VotingPlatform\ElectionPool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ElectionPoolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ElectionPool::class);
    }

    public function findForResult(int $id): ?ElectionPool
    {
        return $this->createQueryBuilder('ep')
            ->select('PARTIAL ep.{id}')
            ->where('ep.id = :id')
            ->andWhere('ep.isSeparator = false')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
