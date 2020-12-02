<?php

namespace App\Repository\ChezVous;

use App\Entity\ChezVous\City;
use App\Entity\ChezVous\Measure;
use App\Entity\ChezVous\MeasureType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class MeasureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
