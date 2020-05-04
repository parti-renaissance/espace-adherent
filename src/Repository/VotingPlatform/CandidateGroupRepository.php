<?php

namespace AppBundle\Repository\VotingPlatform;

use AppBundle\Entity\VotingPlatform\CandidateGroup;
use AppBundle\Entity\VotingPlatform\Election;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CandidateGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidateGroup::class);
    }

    /**
     * @return CandidateGroup[]
     */
    public function findForElection(Election $election): array
    {
        return $this->createQueryBuilder('cg')
            ->addSelect('candidate')
            ->innerJoin('cg.candidates', 'candidate')
            ->where('cg.election = :election')
            ->setParameter('election', $election)
            ->getQuery()
            ->getResult()
        ;
    }
}
