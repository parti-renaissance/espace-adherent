<?php

namespace AppBundle\Repository\Election;

use AppBundle\Entity\City;
use AppBundle\Entity\Election\VoteResultListCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class VoteResultListCollectionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VoteResultListCollection::class);
    }

    public function findOneByCity(City $city): ?VoteResultListCollection
    {
        return $this->createQueryBuilder('lc')
            ->innerJoin('lc.city', 'city')
            ->where('city = :city')
            ->setParameter('city', $city)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByCityInseeCode(string $inseeCode): ?VoteResultListCollection
    {
        return $this->createQueryBuilder('lc')
            ->innerJoin('lc.city', 'city')
            ->where('city.inseeCode = :insee_code')
            ->setParameter('insee_code', $inseeCode)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
