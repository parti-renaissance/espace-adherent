<?php

declare(strict_types=1);

namespace App\Repository\Pronostic;

use App\Entity\Pronostic\Pronostic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pronostic>
 */
class PronosticRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pronostic::class);
    }

    public function findDisplayed(): ?Pronostic
    {
        return $this->createQueryBuilder('pronostic')
            ->where('pronostic.displayed = true')
            ->andWhere('pronostic.beginAt <= :now')
            ->andWhere('DATE_ADD(pronostic.matchAt, 24, \'HOUR\') > :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('pronostic.matchAt', 'ASC')
            ->addOrderBy('pronostic.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findLatest(): ?Pronostic
    {
        return $this->createQueryBuilder('pronostic')
            ->where('pronostic.beginAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('pronostic.matchAt', 'DESC')
            ->addOrderBy('pronostic.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
