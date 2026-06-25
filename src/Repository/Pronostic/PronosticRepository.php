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
            ->setParameter('now', new \DateTimeImmutable())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function unsetDisplayedExcept(Pronostic $pronostic): void
    {
        $this->createQueryBuilder('pronostic')
            ->update()
            ->set('pronostic.displayed', ':notDisplayed')
            ->where('pronostic.id != :id')
            ->setParameter('notDisplayed', false)
            ->setParameter('id', $pronostic->getId())
            ->getQuery()
            ->execute()
        ;
    }
}
