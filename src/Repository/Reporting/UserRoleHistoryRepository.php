<?php

namespace App\Repository\Reporting;

use App\Entity\Reporting\DeclaredMandateHistory;
use App\Entity\Reporting\UserRoleHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRoleHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRoleHistory::class);
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
