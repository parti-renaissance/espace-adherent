<?php

namespace AppBundle\Repository;

use AppBundle\Entity\City;
use AppBundle\Entity\CityVoteResult;
use AppBundle\Entity\ElectionRound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CityVoteResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CityVoteResult::class);
    }

    public function findOneForCity(City $city, ElectionRound $round): ?CityVoteResult
    {
        return $this->createQueryBuilder('cvr')
            ->where('cvr.city = :city')
            ->andWhere('cvr.electionRound = :round')
            ->setParameters([
                'city' => $city,
                'round' => $round,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
