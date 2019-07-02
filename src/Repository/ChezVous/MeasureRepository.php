<?php

namespace AppBundle\Repository\ChezVous;

use AppBundle\Entity\ChezVous\City;
use AppBundle\Entity\ChezVous\Measure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MeasureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Measure::class);
    }

    public function findOneByCityAndType(City $city, string $type): ?Measure
    {
        return $this->findOneBy([
            'city' => $city,
            'type' => $type,
        ]);
    }
}
