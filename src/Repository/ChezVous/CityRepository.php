<?php

namespace App\Repository\ChezVous;

use App\Entity\ChezVous\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function findOneByInseeCode(string $inseeCode): ?City
    {
        return $this->findOneBy(['inseeCode' => City::normalizeCode($inseeCode)]);
    }

    public function findAllByMeasureType(int $measureTypeId): array
    {
        return $this
            ->createQueryBuilder('c')
            ->select('c')
            ->innerJoin('c.measures', 'm')
            ->andWhere('m.type = :type')
            ->setParameter('type', $measureTypeId)
            ->getQuery()
            ->getResult()
        ;
    }
}
