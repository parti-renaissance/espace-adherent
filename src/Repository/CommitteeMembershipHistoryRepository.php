<?php

namespace App\Repository;

use App\Entity\Reporting\CommitteeMembershipHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommitteeMembershipHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommitteeMembershipHistory::class);
    }
}
