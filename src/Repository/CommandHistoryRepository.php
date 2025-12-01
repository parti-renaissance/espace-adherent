<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CommandHistory;
use App\Entity\CommandHistoryTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\CommandHistory>
 */
class CommandHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandHistory::class);
    }

    public function findLastOfType(CommandHistoryTypeEnum $type): ?CommandHistory
    {
        return $this->createQueryBuilder('ch')
            ->setMaxResults(1)
            ->orderBy('ch.createdAt', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
