<?php

namespace App\Repository;

use App\Entity\CommandHistory;
use App\Entity\CommandHistoryTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommandHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandHistory::class);
    }

    public function findLastOfType(CommandHistoryTypeEnum $type): ?CommandHistory
    {
        return $this->createQueryBuilder('ch')
            ->andWhere('ch.type = :type')
            ->setParameter('type', $type)
            ->setMaxResults(1)
            ->orderBy('ch.createdAt', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
