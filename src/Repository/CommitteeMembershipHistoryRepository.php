<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Reporting\CommitteeMembershipHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Reporting\CommitteeMembershipHistory>
 */
class CommitteeMembershipHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommitteeMembershipHistory::class);
    }
}
