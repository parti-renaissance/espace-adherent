<?php

declare(strict_types=1);

namespace App\Repository\Adherent\Activity;

use App\Entity\Adherent\Activity\UserActivityHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserActivityHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserActivityHistory::class);
    }
}
