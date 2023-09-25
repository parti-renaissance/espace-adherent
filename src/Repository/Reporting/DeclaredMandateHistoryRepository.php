<?php

namespace App\Repository\Reporting;

use App\Entity\Adherent;
use App\Entity\Reporting\DeclaredMandateHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class DeclaredMandateHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeclaredMandateHistory::class);
    }

    /**
     * @return DeclaredMandateHistory[]
     */
    public function findNotNotified(): array
    {
        return $this
            ->createNotNotifiedQueryBuilder('history')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNotNotifiedForAdherent(Adherent $adherent): ?DeclaredMandateHistory
    {
        return $this
            ->createNotNotifiedQueryBuilder('history')
            ->andWhere('history.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    private function createNotNotifiedQueryBuilder(string $alias): QueryBuilder
    {
        return $this
            ->createQueryBuilder($alias)
            ->where("$alias.notified = false")
        ;
    }
}
