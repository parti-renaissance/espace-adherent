<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Adherent;
use App\Entity\AdherentSignupSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdherentSignupSourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentSignupSource::class);
    }

    public function existsFor(Adherent $adherent, string $source): bool
    {
        return (bool) $this->count(['adherent' => $adherent, 'source' => $source]);
    }
}
