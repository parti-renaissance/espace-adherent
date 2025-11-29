<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CommitteeCandidaciesGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommitteeCandidaciesGroupRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommitteeCandidaciesGroup::class);
    }
}
