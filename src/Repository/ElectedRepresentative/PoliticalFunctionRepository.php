<?php

declare(strict_types=1);

namespace App\Repository\ElectedRepresentative;

use App\Entity\ElectedRepresentative\PoliticalFunction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PoliticalFunctionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PoliticalFunction::class);
    }
}
