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

    public function findDisplayed(): ?Pronostic
    {
        return $this->findOneBy(['displayed' => true]);
    }

    /** @return Pronostic[] */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('pronostic')
            ->orderBy('pronostic.matchAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
