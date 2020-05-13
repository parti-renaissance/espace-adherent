<?php

namespace App\Repository\ChezVous;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MeasureRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Measure::class);
    }

    public function findOneByCityAndType(City $city, MeasureType $type): ?Measure
    {
        return $this->findOneBy([
            'city' => $city,
            'type' => $type,
        ]);
    }
}
