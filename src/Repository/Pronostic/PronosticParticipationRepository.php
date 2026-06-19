<?php

declare(strict_types=1);

namespace App\Repository\Pronostic;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticParticipation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PronosticParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PronosticParticipation::class);
    }

    public function findFor(Pronostic $pronostic, Adherent $adherent): ?PronosticParticipation
    {
        return $this->findOneBy([
            'pronostic' => $pronostic,
            'adherent' => $adherent,
        ]);
    }

    /**
     * @param Pronostic[] $pronostics
     *
     * @return array<int, PronosticParticipation>
     */
    public function findIndexedByPronostic(Adherent $adherent, array $pronostics): array
    {
        if (!$pronostics) {
            return [];
        }

        $participations = $this->createQueryBuilder('participation')
            ->andWhere('participation.adherent = :adherent')
            ->andWhere('participation.pronostic IN (:pronostics)')
            ->setParameter('adherent', $adherent)
            ->setParameter('pronostics', $pronostics)
            ->getQuery()
            ->getResult()
        ;

        $indexedParticipations = [];
        foreach ($participations as $participation) {
            $indexedParticipations[$participation->pronostic->getId()] = $participation;
        }

        return $indexedParticipations;
    }

    /** @return PronosticParticipation[] */
    public function findAllForPronostic(Pronostic $pronostic): array
    {
        return $this->createQueryBuilder('participation')
            ->addSelect('adherent')
            ->innerJoin('participation.adherent', 'adherent')
            ->andWhere('participation.pronostic = :pronostic')
            ->setParameter('pronostic', $pronostic)
            ->orderBy('participation.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
