<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Committee;
use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeElection;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CommitteeCandidacyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
    public function findConfirmedByCommittee(Committee $committee, Designation $designation): array
    {
        return $this->createConfirmedCandidaciesQueryBuilder($committee, $designation)
            ->getQuery()
            ->getResult()
        ;
    }

    public function hasConfirmedCandidacies(Committee $committee, Designation $designation): bool
    {
        return (bool) $this->createConfirmedCandidaciesQueryBuilder($committee, $designation)
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return CommitteeCandidacy[]
     */
    public function findAllConfirmedForElection(CommitteeElection $election): array
    {
        return $this->createQueryBuilder('candidacy')
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

    private function createConfirmedCandidaciesQueryBuilder(
        Committee $committee,
        Designation $designation,
    ): QueryBuilder {
        return $this->createQueryBuilder('cc')
            ->innerJoin('cc.committeeElection', 'election')
            ->where('election.committee = :committee AND election.designation = :designation')
            ->andWhere('cc.status = :status')
            ->setParameters([
                'committee' => $committee,
                'designation' => $designation,
                'status' => CandidacyInterface::STATUS_CONFIRMED,
            ])
        ;
    }
}
