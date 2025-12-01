<?php

declare(strict_types=1);

namespace App\Repository\Reporting;

use App\Entity\Reporting\DeclaredMandateHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Reporting\DeclaredMandateHistory>
 */
class DeclaredMandateHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeclaredMandateHistory::class);
    }

    /**
     * @return DeclaredMandateHistory[]
     */
    public function findToNotify(): array
    {
        return $this
            ->createQueryBuilder('history')
            ->where('history.notifiedAt IS NULL')
            ->andWhere('history.administrator IS NULL')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return DeclaredMandateHistory[]
     */
    public function findToNotifyOnTelegram(): array
    {
        return $this
            ->createQueryBuilder('history')
            ->where('history.telegramNotifiedAt IS NULL')
            ->getQuery()
            ->getResult()
        ;
    }
}
