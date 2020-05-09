<?php

namespace AppBundle\Repository;

use AppBundle\Entity\CommitteeElection;
use AppBundle\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CommitteeElectionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CommitteeElection::class);
    }

    /**
     * @return CommitteeElection[]
     */
    public function findAllByDesignation(Designation $designation, int $offset = 0, ?int $limit = 200): array
    {
        return $this->createQueryBuilder('ce')
            ->addSelect('c')
            ->innerJoin('ce.committee', 'c')
            ->where('ce.designation = :designation')
            ->setParameter('designation', $designation)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
