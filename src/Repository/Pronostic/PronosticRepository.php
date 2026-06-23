<?php

declare(strict_types=1);

namespace App\Repository\Pronostic;

use App\Entity\Pronostic\Pronostic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PronosticRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pronostic::class);
    }
}
