<?php

namespace App\Repository\TerritorialCouncil;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\Election;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CandidacyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Candidacy::class);
    }

    /**
     * @return Candidacy[]
     */
    public function findAllConfirmedForElection(Election $election): array
    {
        return $this->createQueryBuilder('candidacy')
            ->addSelect('binome')
            ->leftJoin('candidacy.binome', 'binome')
            ->where('candidacy.election = :election')
            ->andWhere('candidacy.status = :confirmed')
            ->setParameters([
                'election' => $election,
                'confirmed' => CandidacyInterface::STATUS_CONFIRMED,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function getCandidatesStats(Election $election): array
    {
        return $this->createQueryBuilder('candidacy')
            ->select('candidacy.quality')
            ->addSelect('COUNT(1) as total')
            ->where('candidacy.election = :election')
            ->andWhere('candidacy.status = :confirmed')
            ->groupBy('candidacy.quality')
            ->setParameters([
                'election' => $election,
                'confirmed' => CandidacyInterface::STATUS_CONFIRMED,
            ])
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
