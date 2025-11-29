<?php

declare(strict_types=1);

namespace App\Repository\Team;

use App\Entity\Phoning\Campaign;
use App\Entity\Team\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function findUsedTeams(array $ids): array
    {
        return array_map('intval', array_column($this->createQueryBuilder('team')
            ->select('DISTINCT team.id')
            ->innerJoin(Campaign::class, 'campaign', Join::WITH, 'campaign.team = team')
            ->where('team.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getScalarResult(), 'id'));
    }
}
