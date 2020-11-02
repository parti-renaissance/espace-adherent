<?php

namespace App\Repository;

use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeElection;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\Designation;
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
    public function findByCommittee(Committee $committee, Designation $designation): array
    {
        return $this->createQueryBuilder('cc')
            ->innerJoin('cc.committeeElection', 'election')
            ->where('election.committee = :committee AND election.designation = :designation')
            ->setParameters([
                'committee' => $committee,
                'designation' => $designation,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return CommitteeCandidacy[]
     */
    public function findAllConfirmedForElection(CommitteeElection $election): array
    {
        return $this->createQueryBuilder('candidacy')
            ->addSelect('binome')
            ->innerJoin('candidacy.binome', 'binome')
            ->where('candidacy.committeeElection = :election')
            ->andWhere('candidacy.status = :confirmed')
            ->setParameters([
                'election' => $election,
                'confirmed' => CandidacyInterface::STATUS_CONFIRMED,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
