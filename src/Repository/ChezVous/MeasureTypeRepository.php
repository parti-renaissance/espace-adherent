<?php

namespace App\Repository\ChezVous;

use App\Entity\ChezVous\MeasureType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MeasureTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MeasureType::class);
    }

    public function findOneByCode(string $code): ?MeasureType
    {
        return $this->findOneBy(['code' => $code]);
    }
}
