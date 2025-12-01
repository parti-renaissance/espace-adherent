<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Reporting\EmailSubscriptionHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Reporting\EmailSubscriptionHistory>
 */
class EmailSubscriptionHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailSubscriptionHistory::class);
    }
}
