<?php

namespace App\Repository\LocalElection;

use App\Entity\LocalElection\LocalElection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LocalElectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalElection::class);
    }

    public function findLastForZones(array $zones): ?LocalElection
    {
        return $this
            ->createQueryBuilder('local_election')
            ->addSelect('CASE WHEN (designation.voteStartDate < :now AND designation.voteEndDate > :now) THEN 1 ELSE 0 END AS HIDDEN score')
            ->innerJoin('local_election.designation', 'designation')
            ->innerJoin('designation.zones', 'zone')
            ->where('zone IN (:zones)')
            ->setParameters([
                'zones' => $zones,
                'now' => new \DateTime(),
            ])
            ->setMaxResults(1)
            ->orderBy('score', 'DESC')
            ->addOrderBy('designation.voteStartDate', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
