<?php

namespace AppBundle\Repository\ChezVous;

use AppBundle\Entity\ChezVous\City;
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
}
