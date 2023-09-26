<?php

namespace App\Repository\Reporting;

use App\Entity\Reporting\DeclaredMandateHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
}
