<?php

namespace AppBundle\Repository\Election;

use AppBundle\Entity\City;
use AppBundle\Entity\Election\VoteResultListCollection;
use AppBundle\Entity\ElectionRound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class VoteResultListCollectionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VoteResultListCollection::class);
    }

    public function findOneByCity(City $city, ElectionRound $electionRound): ?VoteResultListCollection
    {
        return $this->createQueryBuilder('lc')
            ->innerJoin('lc.city', 'city')
            ->where('city = :city AND lc.electionRound = :round')
            ->setParameters([
                'city' => $city,
                'round' => $electionRound,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByCityInseeCode(string $inseeCode, ElectionRound $electionRound): ?VoteResultListCollection
    {
        return $this->createQueryBuilder('lc')
            ->innerJoin('lc.city', 'city')
            ->where('city.inseeCode = :insee_code AND lc.electionRound = :round')
            ->setParameters([
                'insee_code' => $inseeCode,
                'round' => $electionRound,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
