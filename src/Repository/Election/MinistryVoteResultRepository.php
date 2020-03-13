<?php

namespace AppBundle\Repository\Election;

use AppBundle\Entity\City;
use AppBundle\Entity\Election\MinistryVoteResult;
use AppBundle\Entity\ElectionRound;
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
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
