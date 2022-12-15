<?php

namespace App\Repository\LocalElection;

use App\Entity\Geo\Zone;
use App\Entity\LocalElection\LocalElection;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LocalElectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LocalElection::class);
    }

    public function findUpcomingDepartmentElections(): array
    {
        return $this->createQueryBuilder('local_election')
            ->innerJoin('local_election.designation', 'designation')
            ->innerJoin('designation.zones', 'zone')
            ->andWhere('zone.type = :type_department')
            ->setParameter('type_department', Zone::DEPARTMENT)
            ->addOrderBy('zone.code')
            ->getQuery()
            ->getResult()
        ;
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

    public function findByDesignation(Designation $designation): ?LocalElection
    {
        return $this->findOneBy(['designation' => $designation]);
    }
}
