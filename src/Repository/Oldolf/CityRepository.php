<?php

namespace AppBundle\Repository\Oldolf;

use AppBundle\Entity\Oldolf\City;
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
