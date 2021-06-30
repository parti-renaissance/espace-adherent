<?php

namespace App\Repository\Instance\NationalCouncil;

use App\Entity\Instance\NationalCouncil\CandidaciesGroup;
use App\Entity\Instance\NationalCouncil\Election;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CandidaciesGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidaciesGroup::class);
    }

    /**
     * @return CandidaciesGroup[]
     */
    public function findForElection(Election $election): array
    {
        return $this->createQueryBuilder('candidacies_group')
            ->addSelect('candidacy')
            ->innerJoin('candidacies_group.candidacies', 'candidacy')
            ->where('candidacy.election = :election')
            ->setParameter('election', $election)
            ->getQuery()
            ->getResult()
        ;
    }
}
