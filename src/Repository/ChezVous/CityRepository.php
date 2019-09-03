<?php

namespace AppBundle\Repository\ChezVous;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\MeasureType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CityRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, City::class);
    }

    public function findOneByInseeCode(string $inseeCode): ?City
    {
        return $this->findOneBy(['inseeCode' => City::normalizeCode($inseeCode)]);
    }

    public function findAllByMeasureType(MeasureType $measureType): array
    {
        return $this
            ->createQueryBuilder('c')
            ->innerJoin('c.measures', 'm')
            ->andWhere('m.type = :type')
            ->setParameter('type', $measureType)
            ->getQuery()
            ->getResult()
        ;
    }
}
