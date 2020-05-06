<?php

namespace App\Repository\Election;

use App\Entity\City;
use App\Entity\Election\MinistryVoteResult;
use App\Entity\ElectionRound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class MinistryVoteResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MinistryVoteResult::class);
    }

    public function findOneForCity(City $city, ElectionRound $round, bool $onlyUpdated = false): ?MinistryVoteResult
    {
        $qb = $this->createQueryBuilder('mvr')
            ->addSelect('list')
            ->leftJoin('mvr.listTotalResults', 'list')
            ->where('mvr.city = :city')
            ->andWhere('mvr.electionRound = :round')
            ->setParameters([
                'city' => $city,
                'round' => $round,
            ])
        ;

        if ($onlyUpdated) {
            $qb->andWhere('mvr.updatedBy IS NOT NULL');
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllForCity(City $city, array $electionRounds = []): array
    {
        $query = $this->createQueryBuilder('r')
            ->where('r.city = :city')
            ->setParameter('city', $city)
        ;

        if ($electionRounds) {
            $query
                ->andWhere('r.electionRound IN (:rounds)')
                ->setParameter('rounds', $electionRounds)
            ;
        }

        return $query->getQuery()->getResult();
    }
}
