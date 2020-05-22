<?php

namespace App\Repository\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\VoteResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class VoteResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoteResult::class);
    }

    /**
     * @return VoteResult[]
     */
    public function getResults(Election $election): array
    {
        return $this->createQueryBuilder('vr')
            ->addSelect('vc', 'cg', 'c')
            ->innerJoin('vr.voteChoices', 'vc')
            ->leftJoin('vc.candidateGroup', 'cg')
            ->leftJoin('cg.candidates', 'c')
            ->where('vr.election = :election')
            ->setParameter('election', $election)
            ->getQuery()
            ->getResult()
        ;
    }
}
