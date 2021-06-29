<?php

namespace App\Repository\Instance\NationalCouncil;

use App\Entity\Instance\NationalCouncil\Election;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ElectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Election::class);
    }

    public function findByDesignation(Designation $designation): ?Election
    {
        return $this->findOneBy(['designation' => $designation]);
    }

    public function hasActive(): bool
    {
        return 0 < $this->createQueryBuilder('election')
            ->select('COUNT(1)')
            ->innerJoin('election.designation', 'designation')
            ->where('designation.voteEndDate > :now')
            ->setParameters([
                'now' => new \DateTime(),
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findLast(): ?Election
    {
        return $this->createQueryBuilder('election')
            ->innerJoin('election.designation', 'designation')
            ->orderBy('designation.candidacyStartDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
