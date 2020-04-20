<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeCandidacy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CommitteeCandidacyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CommitteeCandidacy::class);
    }

    /**
     * @return CommitteeCandidacy[]
     */
    public function findByCommittee(Committee $committee): array
    {
        return $this->createQueryBuilder('cc')
            ->innerJoin('cc.committeeElection', 'election')
            ->where('election.committee = :committee')
            ->setParameter('committee', $committee)
            ->getQuery()
            ->getResult()
        ;
    }
}
