<?php

namespace AppBundle\Repository\Election;

use AppBundle\Entity\City;
use AppBundle\Entity\Election\CityVoteResult;
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
            ->addSelect('total', 'list')
            ->leftJoin('cvr.listTotalResults', 'total')
            ->leftJoin('total.list', 'list')
            ->where('cvr.city = :city')
            ->andWhere('cvr.electionRound = :round')
            ->setParameters([
                'city' => $city,
                'round' => $round,
            ])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
