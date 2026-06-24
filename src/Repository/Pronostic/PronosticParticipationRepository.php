<?php

declare(strict_types=1);

namespace App\Repository\Pronostic;

use App\Entity\Adherent;
use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticParticipation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PronosticParticipation>
 */
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
}
