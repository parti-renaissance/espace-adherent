<?php

declare(strict_types=1);

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
            ->andWhere('zone.type IN (:types)')
            ->andWhere('designation.voteEndDate > :now
                OR (
                    designation.resultDisplayDelay > 0
                    AND DATE_ADD(designation.voteEndDate, designation.resultDisplayDelay, \'DAY\') > :now
                )')
            ->setParameters(['types' => [Zone::DEPARTMENT, Zone::FOREIGN_DISTRICT], 'now' => new \DateTime()])
            ->addOrderBy('zone.code')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByDesignation(Designation $designation): ?LocalElection
    {
        return $this->findOneBy(['designation' => $designation]);
    }
}
