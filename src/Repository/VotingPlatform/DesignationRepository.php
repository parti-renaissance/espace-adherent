<?php

namespace App\Repository\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class DesignationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Designation::class);
    }

    /**
     * @return Designation[]
     */
    public function getIncomingDesignations(\DateTime $voteStartDate): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.candidacyEndDate < :date AND d.voteStartDate > :date')
            ->setParameter('date', $voteStartDate)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Designation[]
     */
    public function getIncomingCandidacyDesignations(\DateTime $candidacyStartDate): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.candidacyStartDate <= :date AND d.candidacyEndDate > :date')
            ->setParameter('date', $candidacyStartDate)
            ->getQuery()
            ->getResult()
        ;
    }
}
