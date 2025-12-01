<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserActionHistory;
use App\History\UserActionHistoryTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\UserActionHistory>
 */
class UserActionHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserActionHistory::class);
    }

    /**
     * @param UserActionHistoryTypeEnum[] $types
     *
     * @return UserActionHistory[]
     */
    public function findToNotifyOnTelegram(array $types): array
    {
        return $this
            ->createQueryBuilder('history')
            ->where('history.telegramNotifiedAt IS NULL')
            ->andWhere('history.type IN (:types)')
            ->setParameter('types', $types)
            ->getQuery()
            ->getResult()
        ;
    }
}
